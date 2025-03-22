<?php

namespace App\Entity;

use App\Repository\SteamDirectoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SteamDirectoryRepository::class)]
class SteamDirectory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fetchedAt = null;

    #[ORM\Column]
    private array $rawData = [];

    #[ORM\Column]
    private ?int $serverCount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFetchedAt(): ?\DateTimeInterface
    {
        return $this->fetchedAt;
    }

    public function setFetchedAt(\DateTimeInterface $fetchedAt): static
    {
        $this->fetchedAt = $fetchedAt;

        return $this;
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }

    public function setRawData(array $rawData): static
    {
        $this->rawData = $rawData;

        return $this;
    }

    public function getServerCount(): ?int
    {
        return $this->serverCount;
    }

    public function setServerCount(int $serverCount): static
    {
        $this->serverCount = $serverCount;

        return $this;
    }
}
