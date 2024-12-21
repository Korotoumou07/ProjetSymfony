<?php

namespace App\Entity;


use App\Entity\Statut;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Client;

#[ORM\Entity]
class Demande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateAt = null;

    #[ORM\Column(length: 100)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column(enumType: Statut::class)]
    private ?Statut $Statut = null;

    #[ORM\ManyToOne(inversedBy: 'demandes', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $Client = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\ManyToMany(targetEntity: Article::class, inversedBy: 'demandes')]
    private Collection $Article;

    #[ORM\OneToMany(mappedBy: 'Demande', targetEntity: Demande_Article::class, cascade: ['persist', 'remove'])]
private Collection $demande_Article;

    /**
     * @var Collection<int, Relance>
     */
    #[ORM\OneToMany(targetEntity: Relance::class, mappedBy: 'Demande')]
    private Collection $relances;

    public function __construct()
    {
        $this->Article = new ArrayCollection();
        $this->demande_Article = new ArrayCollection();
        $this->relances = new ArrayCollection();
    }

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

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getStatut(): ?Statut
    {
        return $this->Statut;
    }

    public function setStatut(Statut $Statut): static
    {
        $this->Statut = $Statut;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->Client;
    }

    public function setClient(?Client $Client): static
    {
        $this->Client = $Client;

        return $this;
    }

 

    /**
     * @return Collection<int, Article>
     */
    public function getArticle(): Collection
    {
        return $this->Article;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->Article->contains($article)) {
            $this->Article->add($article);
        }

        return $this;
    }

    public function removeArticle(Article $article): static
    {
        $this->Article->removeElement($article);

        return $this;
    }


    public function getDemandeArticle(): Collection
    {
        return $this->demande_Article;
    }
    
    /**
     * @return Collection<int, Relance>
     */
    public function getRelances(): Collection
    {
        return $this->relances;
    }

    public function addRelance(Relance $relance): static
    {
        if (!$this->relances->contains($relance)) {
            $this->relances->add($relance);
            $relance->setDemande($this);
        }

        return $this;
    }

    public function removeRelance(Relance $relance): static
    {
        if ($this->relances->removeElement($relance)) {
            if ($relance->getDemande() === $this) {
                $relance->setDemande(null);
            }
        }

        return $this;
    }

}
