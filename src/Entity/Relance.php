<?php

namespace App\Entity;

use App\Repository\RelanceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RelanceRepository::class)]
class Relance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateAt = null;

    #[ORM\Column(length: 100)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'relances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Demande $Demande = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateAt(): ?\DateTimeImmutable
    {
        return $this->dateAt;
    }

    public function setDateAt(\DateTimeImmutable $dateAt): static
    {
        $this->dateAt = $dateAt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDemande(): ?Demande
    {
        return $this->Demande;
    }

    public function setDemande(?Demande $Demande): static
    {
        $this->Demande = $Demande;

        return $this;
    }
}
