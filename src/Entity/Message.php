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

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $creation_date = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_reported = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $report_reason = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true, enumType: MessageType::class)]
    private ?array $status = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conversation $conversation = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $sender = null;

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

    public function isReported(): ?bool
    {
        return $this->is_reported;
    }

    public function setIsReported(?bool $is_reported): static
    {
        $this->is_reported = $is_reported;

        return $this;
    }

    public function getReportReason(): ?string
    {
        return $this->report_reason;
    }

    public function setReportReason(?string $report_reason): static
    {
        $this->report_reason = $report_reason;

        return $this;
    }

    /**
     * @return MessageType[]|null
     */
    public function getStatus(): ?array
    {
        return $this->status;
    }

    public function setStatus(?array $status): static
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
}
