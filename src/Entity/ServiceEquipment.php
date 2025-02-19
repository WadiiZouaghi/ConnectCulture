<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ServiceEquipmentRepository;

#[ORM\Entity(repositoryClass: ServiceEquipmentRepository::class)]
class ServiceEquipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'text')]
    private $description;
    
    #[ORM\OneToOne(targetEntity: Image::class)]
    #[ORM\JoinColumn(name: "image_id", referencedColumnName: "image_id", nullable: true)]
    private $image;

    #[ORM\ManyToOne(targetEntity: Service::class, inversedBy: 'serviceEquipments')]
    #[ORM\JoinColumn(name: 'service_id', referencedColumnName: 'id')]
    private ?Service $service = null;

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

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;
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
}

