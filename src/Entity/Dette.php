<?php

namespace App\Entity;

use App\Repository\DetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: DetteRepository::class)]

class Dette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column]
    private ?float $montantVerser = null;

    #[ORM\Column]
    private ?float $montantRestant = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateAt = null;

    #[ORM\ManyToOne(inversedBy: 'dettes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $Client = null;

    
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'Dette',cascade: ['persist', 'remove'])]
    private Collection $payments;

    
    #[ORM\ManyToMany(targetEntity: Article::class, inversedBy: 'dettes')]
    private Collection $Article;
    
   
    #[ORM\OneToMany(targetEntity: Dette_Article::class, mappedBy: 'dette', cascade: ['persist', 'remove'])]
    private Collection $dette_Article;

   

    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->Article = new ArrayCollection();
        $this->dette_Article = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMontantVerser(): ?float
    {
        return $this->montantVerser;
    }

    public function setMontantVerser(float $montantVerser): static
    {
        $this->montantVerser = $montantVerser;

        return $this;
    }

    public function getMontantRestant(): ?float
    {
        return $this->montantRestant;
    }

    public function setMontantRestant(float $montantRestant): static
    {
        $this->montantRestant = $montantRestant;

        return $this;
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
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setDette($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getDette() === $this) {
                $payment->setDette(null);
            }
        }

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

    public function getDetteArticle(): Collection
{
    return $this->dette_Article;
}


#[ORM\Column(type: 'boolean', options: ['default' => false])]
private bool $isArchived = false;

public function isArchived(): bool
{
    return $this->isArchived;
}

public function setIsArchived(bool $isArchived): self
{
    $this->isArchived = $isArchived;
    return $this;
}
}
