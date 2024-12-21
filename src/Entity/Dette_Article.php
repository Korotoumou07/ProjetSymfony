<?php

namespace App\Entity;

use App\Repository\DetailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetailRepository::class)]
class Dette_Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne( targetEntity: Dette::class, inversedBy: 'dette_Article')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Dette $Dette = null;


    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'dette_Article')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $Article = null;

    

    #[ORM\Column]
    private ?int $qte = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDette(): ?Dette
    {
        return $this->Dette;
    }

    public function setDette(Dette $Dette): static
    {
        $this->Dette = $Dette;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->Article;
    }

    public function setArticle(Article $Article): static
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
