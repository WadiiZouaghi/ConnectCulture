<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'text')]
    private $description;

    
    #[ORM\OneToMany(mappedBy: 'service', targetEntity: ServiceEquipment::class, cascade: ['persist', 'remove'])]
    private Collection $serviceEquipments;

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
  

    // Getter for the serviceEquipments collection
    public function getServiceEquipments(): Collection
    {
        return $this->serviceEquipments;
    }

    // Add a ServiceEquipment to the collection
    public function addServiceEquipment(ServiceEquipment $equipment): self
    {
        // Avoid duplicates by checking if the equipment is already in the collection
        if (!$this->serviceEquipments->contains($equipment)) {
            $this->serviceEquipments[] = $equipment;
            $equipment->setService($this); // Set the service on the equipment
        }

        return $this;
    }

   
}


