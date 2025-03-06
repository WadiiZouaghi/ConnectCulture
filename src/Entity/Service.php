<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert; // Import validation constraints

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Service name cannot be empty')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Service name must be at least {{ limit }} characters long',
        maxMessage: 'Service name cannot be longer than {{ limit }} characters'
    )]
    private $name;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Description cannot be empty')]
    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: 'Description must be at least {{ limit }} characters long',
        maxMessage: 'Description cannot be longer than {{ limit }} characters'
    )]
    private $description;

    #[ORM\OneToMany(mappedBy: 'service', targetEntity: ServiceEquipment::class, cascade: ['persist', 'remove'])]
    private Collection $serviceEquipments;

    public function __construct()
    {
        $this->serviceEquipments = new ArrayCollection(); // Initialize the collection
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getServiceEquipments(): Collection
    {
        return $this->serviceEquipments;
    }

    public function addServiceEquipment(ServiceEquipment $equipment): self
    {
        if (!$this->serviceEquipments->contains($equipment)) {
            $this->serviceEquipments[] = $equipment;
            $equipment->setService($this);
        }
        return $this;
    }
}
