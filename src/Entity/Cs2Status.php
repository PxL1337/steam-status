<?php

namespace App\Entity;

use App\Repository\Cs2StatusRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: Cs2StatusRepository::class)]
class Cs2Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fetchedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $appVersion = null;

    #[ORM\Column(nullable: true)]
    private ?int $appTimestamp = null;

    #[ORM\Column]
    private array $rawData = [];

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

    public function getAppVersion(): ?int
    {
        return $this->appVersion;
    }

    public function setAppVersion(?int $appVersion): static
    {
        $this->appVersion = $appVersion;

        return $this;
    }

    public function getAppTimestamp(): ?int
    {
        return $this->appTimestamp;
    }

    public function setAppTimestamp(?int $appTimestamp): static
    {
        $this->appTimestamp = $appTimestamp;

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
}
