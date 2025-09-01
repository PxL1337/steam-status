<?php

namespace App\Entity;

use App\Repository\SteamUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: SteamUserRepository::class)]
class SteamUser
{
    #[ORM\Id]
    #[ORM\Column(length: 17)]
    private ?string $steamId;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private ?int $level = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $personaName = null;

    #[ORM\Column(length: 255)]
    private ?string $avatar = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(nullable: true)]
    private ?int $timeCreated = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $vacBanned = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private ?int $vacBanCount = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $communityBanned = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $economyBan = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getSteamId(): ?string
    {
        return $this->steamId;
    }

    public function setSteamId(string $steamId): static
    {
        $this->steamId = $steamId;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getPersonaName(): ?string
    {
        return $this->personaName;
    }

    public function setPersonaName(?string $personaName): static
    {
        $this->personaName = $personaName;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getTimeCreated(): ?int
    {
        return $this->timeCreated;
    }

    public function setTimeCreated(?int $timeCreated): static
    {
        $this->timeCreated = $timeCreated;

        return $this;
    }

    public function isVacBanned(): ?bool
    {
        return $this->vacBanned;
    }

    public function setVacBanned(bool $vacBanned): static
    {
        $this->vacBanned = $vacBanned;

        return $this;
    }

    public function getVacBanCount(): ?int
    {
        return $this->vacBanCount;
    }

    public function setVacBanCount(int $vacBanCount): static
    {
        $this->vacBanCount = $vacBanCount;

        return $this;
    }

    public function isCommunityBanned(): ?bool
    {
        return $this->communityBanned;
    }

    public function setCommunityBanned(bool $communityBanned): static
    {
        $this->communityBanned = $communityBanned;

        return $this;
    }

    public function getEconomyBan(): ?string
    {
        return $this->economyBan;
    }

    public function setEconomyBan(?string $economyBan): static
    {
        $this->economyBan = $economyBan;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
