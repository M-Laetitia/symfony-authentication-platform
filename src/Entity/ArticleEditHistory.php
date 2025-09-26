<?php

namespace App\Entity;

use App\Repository\ArticleEditHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleEditHistoryRepository::class)]
class ArticleEditHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private \DateTimeImmutable $editedAt;
    
    #[ORM\Column(type: Types::JSON, nullable: false)]
    private array $oldContent = [];
    
    #[ORM\Column(type: Types::JSON, nullable: false)]
    private array $newContent = [];
    
    #[ORM\ManyToOne(inversedBy: 'articleEditHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private Article $article;
    
    #[ORM\ManyToOne(inversedBy: 'articleEditHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private User $lastEditBy;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEditedAt(): ?\DateTimeImmutable
    {
        return $this->editedAt;
    }

    public function setEditedAt(\DateTimeImmutable $editedAt): static
    {
        $this->editedAt = $editedAt;

        return $this;
    }

    public function getOldContent(): array
    {
        return $this->oldContent;
    }

    public function setOldContent(array $oldContent): static
    {
        $this->oldContent = $oldContent;

        return $this;
    }

    public function getNewContent(): array
    {
        return $this->newContent;
    }

    public function setNewContent(array $newContent): static
    {
        $this->newContent = $newContent;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function getLastEditBy(): ?User
    {
        return $this->lastEditBy;
    }

    public function setLastEditBy(?User $lastEditBy): static
    {
        $this->lastEditBy = $edilastEditBytor;

        return $this;
    }
}
