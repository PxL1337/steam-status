<?php

// src/Service/SteamUserUpdater.php
namespace App\Service;

use App\Entity\SteamUser;
use Doctrine\ORM\EntityManagerInterface;
use xPaw\Steam\SteamID;

final class SteamUserUpdater
{
    public function __construct(
        private readonly SteamApi $api,
        private readonly EntityManagerInterface $em,
    ) {}

    public function updateFromInput(string $input): SteamUser
    {
        $sid = SteamID::SetFromURL($input, fn(string $v) => $this->api->resolveVanity($v));

        if ($sid->GetAccountType() !== SteamID::TypeIndividual || !$sid->IsValid()) {
            throw new \InvalidArgumentException('SteamID non supporté ou invalide.');
        }
        $sid->SetAccountInstance(SteamID::DesktopInstance)->SetAccountUniverse(SteamID::UniversePublic);
        $steam64 = (string)$sid->ConvertToUInt64();

        $summary = $this->api->getPlayerSummary($steam64);
        $level   = $this->api->getLevel($steam64);
        $bans    = $this->api->getBans($steam64);

        $user = $this->em->getRepository(SteamUser::class)->find($steam64) ?? new SteamUser();
        $now  = new \DateTimeImmutable();

        $user->setSteamId($steam64);
        $user->setPersonaName($summary['personaname'] ?? null);
        $user->setAvatar($summary['avatarfull'] ?? null);
        $user->setCountry($summary['loccountrycode'] ?? null);
        $user->setTimeCreated(isset($summary['timecreated']) ? (int)$summary['timecreated'] : null);

        $user->setLevel($level);
        $user->setVacBanned((bool)($bans['VACBanned'] ?? false));
        $user->setVacBanCount((int)($bans['NumberOfVACBans'] ?? 0));
        $user->setCommunityBanned((bool)($bans['CommunityBanned'] ?? false));
        $user->setEconomyBan($bans['EconomyBan'] ?? null);

        $user->setUpdatedAt($now);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
