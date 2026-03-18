<?php

namespace App\Entity;

use App\Repository\GallerySeriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private Collection $serie;

    public function __construct()
    {
        $this->serie = new ArrayCollection();
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
    public function getSerie(): Collection
    {
        return $this->serie;
    }

    public function addSerie(Media $serie): static
    {
        if (!$this->serie->contains($serie)) {
            $this->serie->add($serie);
            $serie->setGallerySeries($this);
        }

        return $this;
    }

    public function removeSerie(Media $serie): static
    {
        if ($this->serie->removeElement($serie)) {
            // set the owning side to null (unless already changed)
            if ($serie->getGallerySeries() === $this) {
                $serie->setGallerySeries(null);
            }
        }

        return $this;
    }
}
