<?php

namespace App\Entity;

use App\Enum\OrderType;
use App\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, unique: true)]
    private ?string $order_number = null;

    #[ORM\Column(type: 'string', enumType: OrderType::class)]
    private OrderType $status ;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private array $service_snapshot = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $terms_accepted_at = null;

    #[ORM\OneToOne(inversedBy: 'orderProposal', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?ServiceProposal $serviceProposal = null;

    #[ORM\OneToOne(mappedBy: 'orderProposal', cascade: ['persist', 'remove'])]
    private ?Payment $payment = null;


    #[ORM\OneToOne(mappedBy: 'orderProposal', cascade: ['persist', 'remove'])]
    private ?Cancellation $cancellation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\Column]
    private ?int $totalAmount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNumber(): ?string
    {
        return $this->order_number;
    }

    public function setOrderNumber(string $order_number): static
    {
        $this->order_number = $order_number;

        return $this;
    }


    public function getStatus(): OrderType
    {
        return $this->status;
    }

    public function setStatus(OrderType $status): self
    {
        $this->status = $status;
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

    public function getServiceSnapshot(): array
    {
        return $this->service_snapshot;
    }

    public function setServiceSnapshot(array $service_snapshot): static
    {
        $this->service_snapshot = $service_snapshot;

        return $this;
    }

    public function getTermsAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->terms_accepted_at;
    }

    public function setTermsAcceptedAt(\DateTimeImmutable $terms_accepted_at): static
    {
        $this->terms_accepted_at = $terms_accepted_at;

        return $this;
    }

    public function getServiceProposal(): ?ServiceProposal
    {
        return $this->serviceProposal;
    }

    public function setServiceProposal(ServiceProposal $serviceProposal): static
    {
        $this->serviceProposal = $serviceProposal;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(Payment $payment): static
    {
        // set the owning side of the relation if necessary
        if ($payment->getOrderProposal() !== $this) {
            $payment->setOrderProposal($this);
        }

        $this->payment = $payment;

        return $this;
    }


    public function getCancellation(): ?Cancellation
    {
        return $this->cancellation;
    }

    public function setCancellation(?Cancellation $cancellation): static
    {
        // unset the owning side of the relation if necessary
        if ($cancellation === null && $this->cancellation !== null) {
            $this->cancellation->setOrderProposal(null);
        }

        // set the owning side of the relation if necessary
        if ($cancellation !== null && $cancellation->getOrderProposal() !== $this) {
            $cancellation->setOrderProposal($this);
        }

        $this->cancellation = $cancellation;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getTotalAmount(): ?int
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(int $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

}
