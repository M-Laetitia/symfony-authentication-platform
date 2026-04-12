<?php

namespace App\Entity;

use App\Enum\PhotographerStatusType;
use App\Enum\PhotographerVisibilityType;
use App\Repository\PhotographerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
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

    /**
     * @var Collection<int, Conversation>
     */
    #[ORM\OneToMany(targetEntity: Conversation::class, mappedBy: 'photographer')]
    private Collection $conversations;

    #[ORM\Column(length: 150, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?array $profile = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, enumType: PhotographerStatusType::class)]
    private array $status = [];

    #[ORM\Column(type: Types::SIMPLE_ARRAY, enumType: PhotographerVisibilityType::class)]
    private array $visibility = [];

    /**
     * @var Collection<int, Media>
     */
    #[ORM\OneToMany(targetEntity: Media::class, mappedBy: 'photographer')]
    private Collection $media;

    /**
     * @var Collection<int, GallerySeries>
     */
    #[ORM\OneToMany(targetEntity: GallerySeries::class, mappedBy: 'photographer')]
    private Collection $gallerySeries;

    /**
     * @var Collection<int, Speciality>
     */
    #[ORM\ManyToMany(targetEntity: Speciality::class, inversedBy: 'photographers')]
    private Collection $specialities;

    public function __construct()
    {
        $this->serviceProposals = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->media = new ArrayCollection();
        $this->gallerySeries = new ArrayCollection();
        $this->specialities = new ArrayCollection();
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

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): static
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations->add($conversation);
            $conversation->setPhotographer($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        if ($this->conversations->removeElement($conversation)) {
            // set the owning side to null (unless already changed)
            if ($conversation->getPhotographer() === $this) {
                $conversation->setPhotographer(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

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

    public function getProfile(): ?array
    {
        return $this->profile;
    }

    public function setProfile(?array $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    // public function getSpecialties(): array
    // {
    //     $specialties = $this->profile['info']['specialties'] ?? null;
        
    //     if (is_string($specialties)) {
    //         return json_decode($specialties, true) ?? [];
    //     }
        
    //     return is_array($specialties) ? $specialties : [];
    // }

    /**
     * @return PhotographerStatusType[]
     */
    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return PhotographerVisibilityType[]
     */
    public function getVisibility(): array
    {
        return $this->visibility;
    }

    public function setVisibility(array $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(Media $medium): static
    {
        if (!$this->media->contains($medium)) {
            $this->media->add($medium);
            $medium->setPhotographer($this);
        }

        return $this;
    }

    public function removeMedium(Media $medium): static
    {
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getPhotographer() === $this) {
                $medium->setPhotographer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GallerySeries>
     */
    public function getGallerySeries(): Collection
    {
        return $this->gallerySeries;
    }

    public function addGallerySeries(GallerySeries $gallerySeries): static
    {
        if (!$this->gallerySeries->contains($gallerySeries)) {
            $this->gallerySeries->add($gallerySeries);
            $gallerySeries->setPhotographer($this);
        }

        return $this;
    }

    public function removeGallerySeries(GallerySeries $gallerySeries): static
    {
        if ($this->gallerySeries->removeElement($gallerySeries)) {
            // set the owning side to null (unless already changed)
            if ($gallerySeries->getPhotographer() === $this) {
                $gallerySeries->setPhotographer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Speciality>
     */
    public function getSpecialities(): Collection
    {
        return $this->specialities;
    }

    /**
     * Get speciality names as array
     * @return array<string>
     */
    public function getSpecialityNames(): array
    {
        return $this->specialities->map(fn(Speciality $speciality) => $speciality->getName())->toArray();
    }

    public function addSpeciality(Speciality $speciality): static
    {
        if (!$this->specialities->contains($speciality)) {
            $this->specialities->add($speciality);
        }

        return $this;
    }

    public function removeSpeciality(Speciality $speciality): static
    {
        $this->specialities->removeElement($speciality);

        return $this;
    }

    /**
     * Get languages names as array
     * @return array<string>
     */
    public function getLanguagesNames(): array
    {
        $languages = $this->profile['info']['languages'] ?? null;
        
        if (is_string($languages)) {
            return json_decode($languages, true) ?? [];
        }
        
        return is_array($languages) ? $languages : [];
    }

}
