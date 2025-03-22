<?php

namespace App\Service;

use App\Entity\Cs2Status;
use App\Entity\SteamDirectory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SteamStatusService
{
    private HttpClientInterface $client;
    private EntityManagerInterface $em;
    private string $steamApiKey;

    public function __construct(
        HttpClientInterface $client,
        EntityManagerInterface $em,
        string $steamApiKey
    ) {
        $this->client = $client;
        $this->em = $em;
        $this->steamApiKey = $steamApiKey;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function updateCsgoStatus(): ?Cs2Status
    {
        // 1) Appel API ICSGOServers_730
        $url = sprintf(
            'https://api.steampowered.com/ICSGOServers_730/GetGameServersStatus/v1/?key=%s',
            $this->steamApiKey
        );
        $data = $this->client->request('GET', $url)->toArray();

        if (!isset($data['result'])) {
            return null;
        }
        $result = $data['result'];

        // 2) Créer l'entité
        $csgoStatus = new Cs2Status();
        $csgoStatus->setFetchedAt(new \DateTime());
        $csgoStatus->setRawData($result);

        // Champs optionnels
        if (isset($result['app']['version'])) {
            $csgoStatus->setAppVersion((int) $result['app']['version']);
        }
        if (isset($result['app']['timestamp'])) {
            $csgoStatus->setAppTimestamp((int) $result['app']['timestamp']);
        }

        // 3) Persister
        $this->em->persist($csgoStatus);
        $this->em->flush();

        return $csgoStatus;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function updateSteamDirectory(): ?SteamDirectory
    {
        // 1) Appel API ISteamDirectory/GetCMListForConnect
        $url = sprintf(
            'https://api.steampowered.com/ISteamDirectory/GetCMListForConnect/v1/?key=%s',
            $this->steamApiKey
        );
        $data = $this->client->request('GET', $url)->toArray();

        if (!isset($data['response'])) {
            return null;
        }
        $result = $data['response'];

        // 2) Créer l'entité
        $steamDirectory = new SteamDirectory();
        $steamDirectory->setFetchedAt(new \DateTime());
        $steamDirectory->setRawData($result);

        // (Optionnel) calculer le serverCount
        if (isset($result['serverlist'])) {
            $steamDirectory->setServerCount(count($result['serverlist']));
        }

        // 3) Persister
        $this->em->persist($steamDirectory);
        $this->em->flush();

        return $steamDirectory;
    }
}