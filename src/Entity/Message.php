<?php

namespace App\Entity;

use App\Enum\MessageType;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $creation_date = null;

    #[ORM\Column(type: 'string', length: 20, enumType: MessageType::class)]
    private MessageType $status ;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conversation $conversation = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $sender = null;

    #[ORM\OneToOne(targetEntity: ServiceProposal::class, inversedBy: 'associatedMessage', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?ServiceProposal $serviceProposal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeImmutable
    {
        return $this->creation_date;
    }

    public function setCreationDate(\DateTimeImmutable $creation_date): static
    {
        $this->creation_date = $creation_date;

        return $this;
    }

    public function getStatus(): MessageType
    {
        return $this->status;
    }

    public function setStatus(MessageType $status): self
    {
        $this->status = $status;
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

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getServiceProposal(): ?ServiceProposal
    {
        return $this->serviceProposal;
    }

    public function setServiceProposal(?ServiceProposal $serviceProposal): static
    {
        $this->serviceProposal = $serviceProposal;

        return $this;
    }
}
