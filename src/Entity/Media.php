<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\MediaType;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $altText = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    // #[ORM\Column(columnDefinition: "ENUM('cover', 'gallery_image', 'article_image')")]
    // private string $type;

    #[ORM\Column(type: 'string', enumType: MediaType::class)]
    private MediaType $typeImage;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $caption = null;

    #[ORM\ManyToOne(inversedBy: 'medias')]
    private ?Article $article = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $width = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $height = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function setAltText(?string $altText): static
    {
        $this->altText = $altText;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getType(): MediaType
    {
        return $this->typeImage;
    }

    public function setType(MediaType $typeImage): self
    {
        $this->typeImage = $typeImage;
        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): static
    {
        $this->caption = $caption;

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

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function setWidth(?string $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHeight(?string $height): static
    {
        $this->height = $height;

        return $this;
    }
}
