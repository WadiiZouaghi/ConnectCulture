<?php

namespace App\Entity;

use App\Repository\EventtypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventtypeRepository::class)]
class Eventtype
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: Evants::class, mappedBy: 'eventType')]
    private Collection $evants;

    public function __construct()
    {
        $this->evants = new ArrayCollection(); // Correction: $this->events -> $this->evants
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

    public function getEvants(): Collection
    {
        return $this->evants; // Correction: $this->events -> $this->evants
    }

    public function addEvant(Evants $evant): self
    {
        if (!$this->evants->contains($evant)) {
            $this->evants->add($evant);
            $evant->setEventType($this);
        }
        return $this;
    }

    public function removeEvant(Evants $evant): self
    {
        if ($this->evants->removeElement($evant)) {
            if ($evant->getEventType() === $this) {
                $evant->setEventType(null);
            }
        }
        return $this;
    }
}
