<?php

namespace App\Entity;

use App\Enum\ServiceProposalType;
use App\Repository\ServiceProposalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceProposalRepository::class)]
class ServiceProposal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expiration_date = null;

    #[ORM\Column(type: 'string', enumType: ServiceProposalType::class)]
    private ServiceProposalType $status ;

    #[ORM\ManyToOne(inversedBy: 'serviceProposals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Photograph $photograph = null;

    #[ORM\ManyToOne(inversedBy: 'serviceProposals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

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

    public function getExpirationDate(): ?\DateTimeImmutable
    {
        return $this->expiration_date;
    }

    public function setExpirationDate(\DateTimeImmutable $expiration_date): static
    {
        $this->expiration_date = $expiration_date;

        return $this;
    }


    public function getStatus(): ServiceProposalType
    {
        return $this->status;
    }

    public function setStatus(ServiceProposalType $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getPhotograph(): ?Photograph
    {
        return $this->photograph;
    }

    public function setPhotograph(?Photograph $photograph): static
    {
        $this->photograph = $photograph;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;

        return $this;
    }
}
