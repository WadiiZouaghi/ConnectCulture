<?php

namespace App\Entity;

use App\Repository\FiltrageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FiltrageRepository::class)]
class Filtrage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[ORM\Column(length: 100)]
    private ?string $mot_interdit = null;

    #[Assert\Choice(choices: ['remplacer', 'bloquer'], message: "L'action doit être soit 'remplacer' soit 'bloquer'.")]
    #[ORM\Column(length: 50)]
    private ?string $action = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMotInterdit(): ?string
    {
        return $this->mot_interdit;
    }

    public function setMotInterdit(string $mot_interdit): static
    {
        $this->mot_interdit = $mot_interdit;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }
}
