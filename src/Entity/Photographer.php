<?php

namespace App\Entity;

use App\Repository\PhotographerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PhotographerRepository::class)]
class Photographer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $lastName = null;

    #[ORM\OneToOne(inversedBy: 'photographer', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, ServiceProposal>
     */
    #[ORM\OneToMany(targetEntity: ServiceProposal::class, mappedBy: 'photographer')]
    private Collection $serviceProposals;

    public function __construct()
    {
        $this->serviceProposals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, ServiceProposal>
     */
    public function getServiceProposals(): Collection
    {
        return $this->serviceProposals;
    }

    public function addServiceProposal(ServiceProposal $serviceProposal): static
    {
        if (!$this->serviceProposals->contains($serviceProposal)) {
            $this->serviceProposals->add($serviceProposal);
            $serviceProposal->setPhotographer($this);
        }

        return $this;
    }

    public function removeServiceProposal(ServiceProposal $serviceProposal): static
    {
        if ($this->serviceProposals->removeElement($serviceProposal)) {
            // set the owning side to null (unless already changed)
            if ($serviceProposal->getPhotographer() === $this) {
                $serviceProposal->setPhotographer(null);
            }
        }

        return $this;
    }
}
