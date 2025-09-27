<?php

// src/Service/SteamApi.php
namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SteamApi
{
    public function __construct(
        private readonly HttpClientInterface $http,
        #[Autowire(service: 'limiter.steam_api')]
        private readonly RateLimiterFactory $limiter,
        private readonly string $steamApiKey,
    ) {}

    private function acquire(): void
    {
        while (true) {
            $limit = $this->limiter->create()->consume(1);
            if ($limit->isAccepted()) return;
            $retryAt = $limit->getRetryAfter();
            $sleep = max(1, $retryAt->getTimestamp() - time());
            sleep($sleep);
        }
    }

    private function get(string $url): array
    {
        $this->acquire();
        return $this->http->request('GET', $url)->toArray(false);
    }

    public function getPlayerSummary(string $steam64): array
    {
        $url = sprintf(
            'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key=%s&steamids=%s',
            urlencode($this->steamApiKey), urlencode($steam64)
        );
        $data = $this->get($url);
        return $data['response']['players'][0] ?? [];
    }

    public function getLevel(string $steam64): int
    {
        $url = sprintf(
            'https://api.steampowered.com/IPlayerService/GetSteamLevel/v1/?key=%s&steamid=%s',
            urlencode($this->steamApiKey), urlencode($steam64)
        );
        $data = $this->get($url);
        return (int)($data['response']['player_level'] ?? 0);
    }

    public function getBans(string $steam64): array
    {
        $url = sprintf(
            'https://api.steampowered.com/ISteamUser/GetPlayerBans/v1/?key=%s&steamids=%s',
            urlencode($this->steamApiKey), urlencode($steam64)
        );
        $data = $this->get($url);
        return $data['players'][0] ?? [];
    }

    public function resolveVanity(string $vanity): string
    {
        $vanity = trim($vanity);
        if ($vanity === '') {
            throw new \InvalidArgumentException('Vanity vide.');
        }

        $url = sprintf(
            'https://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/?key=%s&vanityurl=%s',
            urlencode($this->steamApiKey),
            urlencode($vanity)
        );
        $data = $this->get($url);

        if (!isset($data['response']['success']) || (int)$data['response']['success'] !== 1) {
            $msg = $data['response']['message'] ?? 'Vanity introuvable';
            throw new \InvalidArgumentException('Impossible de résoudre le vanity : ' . $msg);
        }
        return (string)$data['response']['steamid']; // steam64
    }
}
