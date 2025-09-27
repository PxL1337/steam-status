<?php

namespace App\Controller;

use InvalidArgumentException;
use Psr\Cache\CacheItemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use xPaw\Steam\SteamID;

final class SteamIdController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        private readonly string $steamApiKey, // services.yaml: bind string $steamApiKey: '%env(STEAM_API_KEY)%'
    ) {}

    #[Route('/steamid', name: 'steam_id', options: ['sitemap' => true], methods: ['GET','POST'])]
    public function index(Request $request): Response
    {
        // PRG: Turbo/Drive-friendly
        if ($request->isMethod('POST')) {
            $input = trim((string) $request->request->get('q', ''));
            return $this->redirectToRoute('steam_id', ['q' => $input], Response::HTTP_SEE_OTHER);
        }

        $q = trim((string) $request->query->get('q', ''));
        $error = null;
        $result = null;

        if ($q !== '') {
            try {
                $result = $this->resolve($q);
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return $this->render('steamid/index.html.twig', [
            'q'      => $q,
            'error'  => $error,
            'result' => $result,
        ]);
    }

    // ---------- Core ----------

    private function resolve(string $input): array
    {
        // 1) Parser universel : URLs (id/profiles/user, s.team/p/…), Steam2, Steam3, Steam64, friend code CS2
        $sid = $this->parseSteamId($input);

        // 2) Normalisation recommandée par la doc (évite des univers/instances exotiques)
        if ($sid->GetAccountType() !== SteamID::TypeIndividual) {
            throw new InvalidArgumentException('Nous ne supportons que les comptes individuels.');
        }
        if (!$sid->IsValid()) {
            throw new InvalidArgumentException('SteamID invalide.');
        }
        $sid->SetAccountInstance(SteamID::DesktopInstance);
        $sid->SetAccountUniverse(SteamID::UniversePublic);

        // 3) Rendus
        $steam64   = (string) $sid->ConvertToUInt64();
        $steam2    = $sid->RenderSteam2();
        $steam3    = $sid->RenderSteam3();
        $accountId = (int) $sid->GetAccountID();

        // 4) Profil (pour récupérer vanity si dispo)
        $profile    = $this->fetchPlayerSummaryCached($steam64);
        $profileUrl = $profile['profileurl'] ?? ('https://steamcommunity.com/profiles/' . $steam64);
        $vanity     = null;
        if (\preg_match('~steamcommunity\.com/id/([^/]+)~i', $profileUrl, $m)) {
            $vanity = $m[1];
        }

        // 5) ✅ Valeurs exactes via la lib
        $inviteCode = $sid->RenderSteamInvite();               // ex: "dbr-vhv"
        $shortUrl   = $inviteCode ? 'https://s.team/p/' . $inviteCode : null;
        $friendCode = $sid->RenderCsgoFriendCode();            // ex: "AXH9S-EECC"

        $steamHex   = $this->toSteam3Hex($steam64);

        return [
            'vanity'       => $vanity,
            'accountId'    => $accountId,
            'steam64'      => $steam64,
            'steam2'       => $steam2,
            'steam3'       => $steam3,
            'shortUrl'     => $shortUrl,
            'friendCode'   => $friendCode,
            'steam3hex'    => $steamHex,
            'communityUrl' => $profileUrl,
        ];
    }

    /**
     * Parse n’importe quelle entrée utilisateur grâce à SteamID::SetFromURL().
     * Fallback: essais directs (Steam2/3/64, friend code).
     */
    private function parseSteamId(string $input): SteamID
    {
        $in = trim($input);

        // A) Parsing universel (URLs, s.team/p, steamcommunity.com/user, vanity, etc.)
        try {
            return SteamID::SetFromURL(
                $in,
                // Callback vanity: retourne un steam64 ou lève une exception en cas d’échec
                fn (string $vanity): string => $this->resolveVanity($vanity)
            );
        } catch (\Throwable) {
            // continue vers fallback
        }

        // B) Fallbacks directs
        //  - Friend code CS2 (ex: "AXH9S-EECC")
        if (\preg_match('~^[A-Z2-9]{5}-[A-Z2-9]{5}$~', $in)) {
            $sid = new SteamID();
            $sid->SetFromCsgoFriendCode($in);
            return $sid;
        }

        //  - Steam2 / Steam3 / Steam64
        try {
            return new SteamID($in);
        } catch (\Throwable) {
            throw new InvalidArgumentException('Impossible d’interpréter l’entrée fournie.');
        }
    }

    // ---------- API Steam (avec cache) ----------

    private function resolveVanity(string $vanity): string
    {
        $vanity = trim($vanity);
        if ($vanity === '') {
            throw new InvalidArgumentException('Veuillez saisir un identifiant.');
        }

        $cacheKey = 'steam_vanity_' . md5($vanity);

        return $this->cache->get($cacheKey, function (CacheItemInterface $item) use ($vanity): string {
            $item->expiresAfter(600); // 10 minutes

            $url = sprintf(
                'https://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/?key=%s&vanityurl=%s',
                urlencode($this->steamApiKey),
                urlencode($vanity)
            );
            $data = $this->httpClient->request('GET', $url)->toArray(false);

            if (!isset($data['response']['success']) || (int) $data['response']['success'] !== 1) {
                $msg = $data['response']['message'] ?? 'Vanity introuvable';
                throw new InvalidArgumentException('Impossible de résoudre le vanity : ' . $msg);
            }
            return (string) $data['response']['steamid']; // <- steam64 pour SetFromURL
        });
    }

    private function fetchPlayerSummaryCached(string $steam64): array
    {
        $cacheKey = 'steam_summary_' . $steam64;

        return $this->cache->get($cacheKey, function (CacheItemInterface $item) use ($steam64): array {
            $item->expiresAfter(600); // 10 minutes
            try {
                $url = sprintf(
                    'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key=%s&steamids=%s',
                    urlencode($this->steamApiKey),
                    urlencode($steam64)
                );
                $data = $this->httpClient->request('GET', $url)->toArray(false);
                return $data['response']['players'][0] ?? [];
            } catch (\Throwable) {
                return [];
            }
        });
    }

    // ---------- Formats complémentaires (pour la capture) ----------

    private function toSteam3Hex(string $steam64): string
    {
        if (\function_exists('gmp_init')) {
            $hex = \gmp_strval(\gmp_init($steam64, 10), 16);
        } else {
            // fallback simple (ok sur PHP 64 bits)
            $hex = \strtolower(\dechex((int) $steam64));
        }
        if (\strlen($hex) % 2 === 1) {
            $hex = '0' . $hex;
        }
        return 'steam:' . $hex;
    }
}
