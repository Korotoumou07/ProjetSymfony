<?php

namespace App\Entity;

use App\Entity\Demande;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\DetailADRepository;

// #[ORM\Entity(repositoryClass: DetailADRepository::class)]
// class DetailAD
// {
//     #[ORM\Id]
//     #[ORM\GeneratedValue]
//     #[ORM\Column]
//     private ?int $id = null;

//     #[ORM\OneToOne(cascade: ['persist', 'remove'], inversedBy: 'detailADs')]
//     #[ORM\JoinColumn(nullable: false)]
//     private ?Demande $Demande = null;

//     #[ORM\OneToOne(cascade: ['persist', 'remove'], inversedBy: 'detailADs')]
//     #[ORM\JoinColumn(nullable: false)]
//     private ?Article $Article = null;

//     #[ORM\Column]
//     private ?int $qte = null;

//     public function getId(): ?int
//     {
//         return $this->id;
//     }

//     public function getDemande(): ?Demande
//     {
//         return $this->Demande;
//     }

//     public function setDemande(?Demande $Demande): static
//     {
//         $this->Demande = $Demande;

//         return $this;
//     }

//     public function getArticle(): ?Article
//     {
//         return $this->Article;
//     }

//     public function setArticle(?Article $Article): static
//     {
//         $this->Article = $Article;

//         return $this;
//     }

//     public function getQte(): ?int
//     {
//         return $this->qte;
//     }

//     public function setQte(int $qte): static
//     {
//         $this->qte = $qte;

//         return $this;
//     }
// }


namespace App\Entity;

use App\Entity\Demande;

use App\Repository\DetailADRepository;
use App\Entity\Article;



use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetailADRepository::class)]
#[ORM\Table(name: "demande_article")]
class Demande_Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Demande::class, inversedBy: 'Demande_Article')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Demande $Demande = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'Demande_Article')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $Article = null;

    #[ORM\Column]
    private ?int $qte = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getArticle(): ?Article
    {
        return $this->Article;
    }

    public function setArticle(?Article $Article): static
    {
        $this->Article = $Article;

        return $this;
    }

    public function getQte(): ?int
    {
        return $this->qte;
    }

    public function setQte(int $qte): static
    {
        $this->qte = $qte;

        return $this;
    }
}
