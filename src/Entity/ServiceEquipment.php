<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ServiceEquipmentRepository;
use Symfony\Component\Validator\Constraints as Assert; // Import validation constraints

#[ORM\Entity(repositoryClass: ServiceEquipmentRepository::class)]
class ServiceEquipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Equipment name cannot be empty')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Equipment name must be at least {{ limit }} characters long',
        maxMessage: 'Equipment name cannot be longer than {{ limit }} characters'
    )]
    private $name;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Description cannot be empty')]
    #[Assert\Length(
        min: 10,
        max: 500,
        minMessage: 'Description must be at least {{ limit }} characters long',
        maxMessage: 'Description cannot be longer than {{ limit }} characters'
    )]
    private $description;

    #[ORM\ManyToOne(targetEntity: Service::class, inversedBy: 'serviceEquipments')]
    #[ORM\JoinColumn(name: 'service_id', referencedColumnName: 'id')]
    private ?Service $service = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

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

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }
}
