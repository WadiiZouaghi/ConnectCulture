<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Pannier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Evants::class, inversedBy: 'paniers')]  // Corrigé 'Event' en 'Evants'
    private Collection $event;  // Corrigé 'events' en 'evants'

    public function __construct()
    {
        $this->evants = new ArrayCollection();  // Corrigé 'events' en 'evants'
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

    public function getEvants(): Collection  // Corrigé 'events' en 'evants'
    {
        return $this->event;  // Corrigé 'events' en 'evants'
    }

    public function addEvant(Evants $event): self  // Corrigé 'addEvent' en 'addEvant'
    {
        if (!$this->event->contains($event)) {
            $this->event->add($event);
            $event->addPanier($this);
        }
        return $this;
    }

    public function removeEvant(Event $event): self  // Corrigé 'removeEvent' en 'removeEvant'
    {
        if ($this->event->removeElement($event)) {
            $event->removePannier($this);
        }
        return $this;
    }
}
