<?php

namespace App\Entity;

use App\Enum\GallerySeriesType;
use App\Repository\GallerySeriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GallerySeriesRepository::class)]
class GallerySeries
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 125)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'gallerySeries')]
    private ?Photographer $photographer = null;

    /**
     * @var Collection<int, Media>
     */
    #[ORM\OneToMany(targetEntity: Media::class, mappedBy: 'gallerySeries')]
    private Collection $medias;

    #[ORM\Column(type: 'string', enumType: GallerySeriesType::class)]
    private GallerySeriesType $type;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->medias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPhotographer(): ?Photographer
    {
        return $this->photographer;
    }

    public function setPhotographer(?Photographer $photographer): static
    {
        $this->photographer = $photographer;

        return $this;
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media): static
    {
        if (!$this->medias->contains($media)) {
            $this->medias->add($media);
            $media->setGallerySeries($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): static
    {
        if ($this->medias->removeElement($media)) {
            if ($media->getGallerySeries() === $this) {
                $media->setGallerySeries(null);
            }
        }

        return $this;
    }


    public function getType(): GallerySeriesType
    {
        return $this->type;
    }

    public function setType(GallerySeriesType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
