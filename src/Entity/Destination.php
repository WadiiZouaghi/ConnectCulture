<?php

namespace App\Entity;

use App\Repository\DestinationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DestinationRepository::class)]
class Destination
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 6, nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 6, nullable: true)]
    private ?float $latitude = null;

    #[ORM\OneToMany(targetEntity: Evants::class, mappedBy: 'destination')]
    private Collection $evants;

    public function __construct()
    {
        $this->evants = new ArrayCollection();
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

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getEvants(): Collection
    {
        return $this->evants;
    }

    public function addEvant(Evants $evant): self
    {
        if (!$this->evants->contains($evant)) {
            $this->evants->add($evant);
            $evant->setDestination($this);
        }
        return $this;
    }

    public function removeEvant(Evants $evant): self
    {
        if ($this->evants->removeElement($evant)) {
            if ($evant->getDestination() === $this) {
                $evant->setDestination(null);
            }
        }
        return $this;
    }
}
