<?php

namespace App\Entity;

use App\Enum\PricingPlanType;
use App\Repository\PricingPlanRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PricingPlanRepository::class)]
class PricingPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $duration = null;

    #[ORM\Column(options: ['default' => true])]
    private ?bool $isActive = true;

    #[ORM\Column(type: Types::JSON)]
    private array $whatIncluded = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $additionnalInfos = null;

    #[ORM\Column(enumType: PricingPlanType::class)]
    private ?PricingPlanType $planType = null;

    #[ORM\ManyToOne(inversedBy: 'pricingPlans')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Photographer $photographer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getWhatIncluded(): ?array
    {
        return $this->whatIncluded;
    }

    public function setWhatIncluded(?array $whatIncluded): static
    {
        $this->whatIncluded = $whatIncluded;

        return $this;
    }

    public function getAdditionnalInfos(): ?array
    {
        return $this->additionnalInfos;
    }

    public function setAdditionnalInfos(?array $additionnalInfos): static
    {
        $this->additionnalInfos = $additionnalInfos;

        return $this;
    }

    public function getPlanType(): ?PricingPlanType
    {
        return $this->planType;
    }

    public function setPlanType(PricingPlanType $planType): static
    {
        $this->planType = $planType;

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
}
