<?php

namespace App\Entity;

use App\Repository\SpecialityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpecialityRepository::class)]
class Speciality
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Photographer>
     */
    #[ORM\ManyToMany(targetEntity: Photographer::class, mappedBy: 'specialities')]
    private Collection $photographers;

    public function __construct()
    {
        $this->photographers = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Photographer>
     */
    public function getPhotographers(): Collection
    {
        return $this->photographers;
    }

    public function addPhotographer(Photographer $photographer): static
    {
        if (!$this->photographers->contains($photographer)) {
            $this->photographers->add($photographer);
            $photographer->addSpeciality($this);
        }

        return $this;
    }

    public function removePhotographer(Photographer $photographer): static
    {
        if ($this->photographers->removeElement($photographer)) {
            $photographer->removeSpeciality($this);
        }

        return $this;
    }
}
