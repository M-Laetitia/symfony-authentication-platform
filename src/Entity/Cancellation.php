<?php

namespace App\Entity;

use App\Enum\CancellationReasonType;
use App\Repository\CancellationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CancellationRepository::class)]
class Cancellation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $requestedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(type: 'string', enumType: CancellationReasonType::class)]
    private CancellationReasonType $reasonType;

    #[ORM\Column(length: 30)]
    private ?string $cancellation_number = null;

    #[ORM\OneToOne(inversedBy: 'cancellation', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $orderProposal = null;

    #[ORM\OneToOne(inversedBy: 'cancellation', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Invoice $invoice = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequestedAt(): ?\DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeImmutable $requestedAt): static
    {
        $this->requestedAt = $requestedAt;

        return $this;
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


    public function getReasonType(): CancellationReasonType
    {
        return $this->reasonType;
    }

    public function setReasonType(CancellationReasonType $reasonType): self
    {
        $this->reasonType = $reasonType;

        return $this;
    }

    public function getCancellationNumber(): ?string
    {
        return $this->cancellation_number;
    }

    public function setCancellationNumber(string $cancellation_number): static
    {
        $this->cancellation_number = $cancellation_number;

        return $this;
    }

    public function getOrderProposal(): ?Order
    {
        return $this->orderProposal;
    }

    public function setOrderProposal(?Order $orderProposal): static
    {
        $this->orderProposal = $orderProposal;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(Invoice $invoice): static
    {
        $this->invoice = $invoice;

        return $this;
    }


}
