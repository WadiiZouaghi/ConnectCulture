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
    private Collection $event;

    public function __construct()
    {
        $this->event = new ArrayCollection(); // Correction: $this->events -> $this->evants
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
        return $this->event; // Correction: $this->events -> $this->evants
    }

    public function addEvant(Evants $event): self
    {
        if (!$this->event->contains($event)) {
            $this->event->add($event);
            $event->setEventType($this);
        }
        return $this;
    }

    public function removeEvant(Event $event): self
    {
        if ($this->event->removeElement($event)) {
            if ($event->getEventType() === $this) {
                $event->setEventType(null);
            }
        }
        return $this;
    }
}
