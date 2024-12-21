<?php

namespace App\Entity;

use App\Entity\Demande;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25,unique:true)]
    private ?string $nomArticle = null;

    #[ORM\Column]
    private ?int $qteStock = null;

    
    #[ORM\ManyToMany(targetEntity: Demande::class, mappedBy: 'Article')]
    private Collection $demandes;

    
    #[ORM\ManyToMany(targetEntity: Dette::class, mappedBy: 'Article')]
    private Collection $dettes;



#[ORM\OneToMany(targetEntity: Demande_Article::class, mappedBy: 'Article', cascade: ['persist', 'remove'])]
private Collection $demande_Article;


#[ORM\OneToMany(targetEntity: Dette_Article::class,mappedBy: 'Article', cascade: ['persist', 'remove'])]
private Collection $dette_Article;

    #[ORM\Column]
    private ?float $prix = null;

    public function __construct()
    {
        $this->demandes = new ArrayCollection();
        $this->dettes = new ArrayCollection();
        $this->dette_Article = new ArrayCollection();
        $this->demande_Article = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomArticle(): ?string
    {
        return $this->nomArticle;
    }

    public function setNomArticle(string $nomArticle): static
    {
        $this->nomArticle = $nomArticle;

        return $this;
    }

    public function getQteStock(): ?int
    {
        return $this->qteStock;
    }

    public function setQteStock(int $qteStock): static
    {
        $this->qteStock = $qteStock;

        return $this;
    }

    /**
     * @return Collection<int, Demande>
     */
    public function getDemandes(): Collection
    {
        return $this->demandes;
    }

    public function addDemande(Demande $demande): static
    {
        if (!$this->demandes->contains($demande)) {
            $this->demandes->add($demande);
            $demande->addArticle($this);
        }

        return $this;
    }

    public function removeDemande(Demande $demande): static
    {
        if ($this->demandes->removeElement($demande)) {
            $demande->removeArticle($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Dette>
     */
    public function getDettes(): Collection
    {
        return $this->dettes;
    }

    public function addDette(Dette $dette): static
    {
        if (!$this->dettes->contains($dette)) {
            $this->dettes->add($dette);
            $dette->addArticle($this);
        }

        return $this;
    }

    public function removeDette(Dette $dette): static
    {
        if ($this->dettes->removeElement($dette)) {
            $dette->removeArticle($this);
        }

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }
    public function getDetteArticle(): Collection
{
    return $this->dette_Article;
}




    
     public function getDemandeArticle(): Collection
     {
         return $this->demande_Article;
     }
  


}
