<?php

namespace App\Entity;

use App\Enum\ConversationReportType;
use App\Repository\ConversationReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationReportRepository::class)]
class ConversationReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $messageReference = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adminNotes = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true, enumType: ConversationReportType::class)]
    private ?array $status = null;

    #[ORM\ManyToOne(inversedBy: 'conversationReports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $reportedBy = null;

    #[ORM\ManyToOne(inversedBy: 'conversationReports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conversation $conversation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function getMessageReference(): ?string
    {
        return $this->messageReference;
    }

    public function setMessageReference(?string $messageReference): static
    {
        $this->messageReference = $messageReference;

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

    public function getAdminNotes(): ?string
    {
        return $this->adminNotes;
    }

    public function setAdminNotes(?string $adminNotes): static
    {
        $this->adminNotes = $adminNotes;

        return $this;
    }

    /**
     * @return ConversationReportType[]|null
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

    public function getReportedBy(): ?User
    {
        return $this->reportedBy;
    }

    public function setReportedBy(?User $reportedBy): static
    {
        $this->reportedBy = $reportedBy;

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
}
