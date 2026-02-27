<?php

namespace App\Entity;

use App\Enum\InvoiceType;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $issuedAt = null;

    #[ORM\Column(type: 'string', enumType: InvoiceType::class)]
    private InvoiceType $status;

    #[ORM\Column]
    private array $seller_snapshot = [];

    #[ORM\Column]
    private array $buyer_snapshot = [];

    #[ORM\Column]
    private ?int $totalAmount = null;

    #[ORM\Column]
    private array $BillingAddress = [];

    #[ORM\Column(length: 255)]
    private ?string $PdfPath = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isArchived = null;

    #[ORM\Column(length: 30 , unique:true)]
    private ?string $invoice_number = null;

    #[ORM\OneToOne(inversedBy: 'invoice', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $orderProposal = null;

    #[ORM\OneToOne(mappedBy: 'invoice', targetEntity: Cancellation::class)]
    private ?Cancellation $cancellation = null;

    #[ORM\Column]
    private array $order_snapshot = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIssuedAt(): ?\DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(\DateTimeImmutable $issuedAt): static
    {
        $this->issuedAt = $issuedAt;

        return $this;
    }


    public function getStatus(): InvoiceType
    {
        return $this->status;
    }

    public function setStatus(InvoiceType $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getSellerSnapshot(): array
    {
        return $this->seller_snapshot;
    }

    public function setSellerSnapshot(array $seller_snapshot): static
    {
        $this->seller_snapshot = $seller_snapshot;

        return $this;
    }

    public function getBuyerSnapshot(): array
    {
        return $this->buyer_snapshot;
    }

    public function setBuyerSnapshot(array $buyer_snapshot): static
    {
        $this->buyer_snapshot = $buyer_snapshot;

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

    public function getBillingAddress(): array
    {
        return $this->BillingAddress;
    }

    public function setBillingAddress(array $BillingAddress): static
    {
        $this->BillingAddress = $BillingAddress;

        return $this;
    }

    public function getPdfPath(): ?string
    {
        return $this->PdfPath;
    }

    public function setPdfPath(string $PdfPath): static
    {
        $this->PdfPath = $PdfPath;

        return $this;
    }

    public function getIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(?bool $isArchived): static
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoice_number;
    }

    public function setInvoiceNumber(string $invoice_number): static
    {
        $this->invoice_number = $invoice_number;

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

    public function getCancellation(): ?Cancellation
    {
        return $this->cancellation;
    }

    public function setCancellation(Cancellation $cancellation): static
    {
        // set the owning side of the relation if necessary
        if ($cancellation->getInvoice() !== $this) {
            $cancellation->setInvoice($this);
        }

        $this->cancellation = $cancellation;

        return $this;
    }

    public function getOrderSnapshot(): array
    {
        return $this->order_snapshot;
    }

    public function setOrderSnapshot(array $order_snapshot): static
    {
        $this->order_snapshot = $order_snapshot;

        return $this;
    }
}
