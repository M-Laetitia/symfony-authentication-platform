<?php

namespace App\Entity;

use App\Enum\PaymentProviderType;
use App\Enum\PaymentStatusType;
use App\Repository\PaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column(type: 'string', enumType: PaymentProviderType::class)]
    private PaymentProviderType $provider;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $currency = null;

    #[ORM\Column(type: 'string', enumType: PaymentStatusType::class)]
    private PaymentStatusType $status ;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $transactionId = null;

    #[ORM\OneToOne(inversedBy: 'payment', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $orderProposal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(\DateTimeImmutable $paidAt): static
    {
        $this->paidAt = $paidAt;

        return $this;
    }


    public function getProvider(): PaymentProviderType
    {
        return $this->provider;
    }

    public function setProvider(PaymentProviderType $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getStatus(): PaymentStatusType
    {
        return $this->status;
    }

    public function setStatus(PaymentStatusType $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(string $transactionId): static
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    public function getOrderProposal(): ?Order
    {
        return $this->orderProposal;
    }

    public function setOrderProposal(Order $orderProposal): static
    {
        $this->orderProposal = $orderProposal;

        return $this;
    }
}
