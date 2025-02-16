<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Evants::class, inversedBy: 'paniers')]  // Corrigé 'Event' en 'Evants'
    private Collection $evants;  // Corrigé 'events' en 'evants'

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
        return $this->evants;  // Corrigé 'events' en 'evants'
    }

    public function addEvant(Evants $evant): self  // Corrigé 'addEvent' en 'addEvant'
    {
        if (!$this->evants->contains($evant)) {
            $this->evants->add($evant);
            $evant->addPanier($this);
        }
        return $this;
    }

    public function removeEvant(Evants $evant): self  // Corrigé 'removeEvent' en 'removeEvant'
    {
        if ($this->evants->removeElement($evant)) {
            $evant->removePanier($this);
        }
        return $this;
    }
}
