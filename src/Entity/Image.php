<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $imageName = null;

    #[ORM\Column(length: 255)]
    private ?string $imageUrl = null;

    #[ORM\ManyToMany(targetEntity: Evants::class, mappedBy: 'images')]
    private Collection $event;  // Corrigé 'events' en 'evants'

    public function __construct()
    {
        $this->event = new ArrayCollection();  // Corrigé 'events' en 'evants'
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(string $imageName): self
    {
        $this->imageName = $imageName;
        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;
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
            $event->addImage($this);
        }
        return $this;
    }

    public function removeEvant(Evants $event): self  // Corrigé 'removeEvent' en 'removeEvant'
    {
        if ($this->event->removeElement($event)) {
            $event->removeImage($this);
        }
        return $this;
    }
}
