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

    #[ORM\ManyToOne(inversedBy: 'serviceProposals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conversation $conversation = null;

    #[ORM\OneToOne(mappedBy: 'serviceProposal', cascade: ['persist', 'remove'])]
    private ?Message $associatedMessage = null;

    #[ORM\Column]
    private ?float $price_exclu_tax = null;

    #[ORM\ManyToOne(inversedBy: 'serviceProposals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tax $tax = null;


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

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): static
    {
        $this->conversation = $conversation;

        return $this;
    }

    public function getAssociatedMessage(): ?Message
    {
        return $this->associatedMessage;
    }

    public function setAssociatedMessage(?Message $associatedMessage): static
    {
        if ($associatedMessage === null && $this->associatedMessage !== null) {
            $this->associatedMessage->setServiceProposal(null);
        }

        if ($associatedMessage !== null && $associatedMessage->getServiceProposal() !== $this) {
            $associatedMessage->setServiceProposal($this);
        }

        $this->associatedMessage = $associatedMessage;

        return $this;
    }

    public function getpriceExcluTax(): ?float
    {
        return $this->price_exclu_tax;
    }

    public function setpriceExcluTax(float $price_exclu_tax): static
    {
        $this->price_exclu_tax = $price_exclu_tax;

        return $this;
    }

    public function getTax(): ?Tax
    {
        return $this->tax;
    }

    public function setTax(?Tax $tax): static
    {
        $this->tax = $tax;

        return $this;
    }
}
