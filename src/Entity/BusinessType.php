<?php

namespace App\Entity;

use App\Repository\BusinessTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BusinessTypeRepository::class)]
class BusinessType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    /**
     * @var Collection<int, Business>
     */
    #[ORM\OneToMany(targetEntity: Business::class, mappedBy: 'business_type')]
    private Collection $business;

    public function __construct()
    {
        $this->business = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Business>
     */
    public function getBusiness(): Collection
    {
        return $this->business;
    }

    public function addBusiness(Business $business): static
    {
        if (!$this->business->contains($business)) {
            $this->business->add($business);
            $business->setBusinessType($this);
        }

        return $this;
    }

    public function removeBusiness(Business $business): static
    {
        if ($this->business->removeElement($business)) {
            // set the owning side to null (unless already changed)
            if ($business->getBusinessType() === $this) {
                $business->setBusinessType(null);
            }
        }

        return $this;
    }
}
