<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Filtrage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'This field is required')]
    private ?string $mot_interdit = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'Please select an action')]
    private ?string $action = null;

    // Getters and Setters
    public function getId(): ?int { return $this->id; }

    public function getMotInterdit(): ?string { return $this->mot_interdit; }
    public function setMotInterdit(string $mot_interdit): self
    {
        $this->mot_interdit = $mot_interdit;
        return $this;
    }

    public function getAction(): ?string { return $this->action; }
    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }
}
