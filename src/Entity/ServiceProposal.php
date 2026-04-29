<?php

namespace App\Entity;

use App\Enum\ServiceProposalType;
use App\Repository\ServiceProposalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ServiceProposalRepository::class)]
#[Assert\Callback('validateExpirationDate')]
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
    private ?Photographer $photographer = null;

    #[ORM\ManyToOne(inversedBy: 'serviceProposals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\ManyToOne(inversedBy: 'serviceProposals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conversation $conversation = null;

    #[ORM\OneToOne(mappedBy: 'serviceProposal', cascade: ['persist', 'remove'])]
    private ?Message $associatedMessage = null;

    // #[ORM\Column]
    // private ?float $price_exclu_tax = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price_exclu_tax = null;

    #[ORM\ManyToOne(inversedBy: 'serviceProposals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tax $tax = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'serviceProposal')]
    private Collection $orders;

    #[ORM\Column]
    private ?int $deliveryDelay = null;

    #[ORM\Column]
    private ?int $editedPhotoCount = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $endAt = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $serviceDate = null;

    #[ORM\Column(length: 255)]
    private ?string $location = null;


    #[ORM\ManyToOne(inversedBy: 'serviceProposals')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Speciality $speciality = null;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

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

    public function getPhotographer(): ?Photographer
    {
        return $this->photographer;
    }

    public function setPhotographer(?Photographer $photographer): static
    {
        $this->photographer = $photographer;

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

    public function getPriceExcluTax(): ?string
    {
        return $this->price_exclu_tax;
    }

    public function setPriceExcluTax(string $price_exclu_tax): static
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

    public function getPriceTtc(): ?float
    {
        if ($this->price_exclu_tax === null || $this->tax === null) {
            return null;
        }

        return $this->price_exclu_tax * (1 + $this->tax->getRate());
    }

    public function getTaxAmount(): ?float
    {
        if ($this->price_exclu_tax === null || $this->tax === null) {
            return null;
        }

        return $this->price_exclu_tax * $this->tax->getRate();
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setServiceProposal($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        $this->orders->removeElement($order);

        return $this;
    }

    public function getDeliveryDelay(): ?int
    {
        return $this->deliveryDelay;
    }

    public function setDeliveryDelay(int $deliveryDelay): static
    {
        $this->deliveryDelay = $deliveryDelay;

        return $this;
    }

    public function getEditedPhotoCount(): ?int
    {
        return $this->editedPhotoCount;
    }

    public function setEditedPhotoCount(int $editedPhotoCount): static
    {
        $this->editedPhotoCount = $editedPhotoCount;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeImmutable $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeImmutable $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getServiceDate(): ?\DateTimeImmutable
    {
        return $this->serviceDate;
    }

    public function setServiceDate(\DateTimeImmutable $serviceDate): static
    {
        $this->serviceDate = $serviceDate;

        return $this;
    }

    public function getSpeciality(): ?Speciality
    {
        return $this->speciality;
    }

    public function setSpeciality(?Speciality $speciality): static
    {
        $this->speciality = $speciality;

        return $this;
    }

    /**
     * Validate that expiration date is before service date
     */
    public function validateExpirationDate(ExecutionContextInterface $context): void
    {
        if ($this->expiration_date && $this->serviceDate) {
            if ($this->expiration_date > $this->serviceDate) {
                $context->buildViolation('Expiration date must be before the service date.')
                    ->atPath('expiration_date')
                    ->addViolation();
            }
        }
    }
}
