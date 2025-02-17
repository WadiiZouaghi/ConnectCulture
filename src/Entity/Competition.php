<?php

namespace App\Entity;

use App\Repository\CompetitionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompetitionRepository::class)]
class Competition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $Id_comp = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $Nom = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $Description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "Veuillez entrer une date de début.")]
    private ?\DateTimeInterface $Date_debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "Veuillez entrer une date de fin.")]
    #[Assert\GreaterThan(
        propertyPath: "Date_debut",
        message: "La date de fin doit être postérieure à la date de début."
    )]
    private ?\DateTimeInterface $Date_fin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Etat = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Veuillez sélectionner un organisateur.")]
    #[Assert\Positive(message: "L'ID de l'organisateur doit être un nombre positif.")]
    private ?int $Organisateur_ID = null;

    // Getters et Setters
    public function getIdComp(): ?int
    {
        return $this->Id_comp;
    }

    public function setIdComp(int $Id_comp): static
    {
        $this->Id_comp = $Id_comp;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): static
    {
        $this->Nom = $Nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): static
    {
        $this->Description = $Description;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->Date_debut;
    }

    public function setDateDebut(\DateTimeInterface $Date_debut): static
    {
        $this->Date_debut = $Date_debut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->Date_fin;
    }

    public function setDateFin(\DateTimeInterface $Date_fin): static
    {
        $this->Date_fin = $Date_fin;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->Etat;
    }

    public function setEtat(?string $Etat): static
    {
        $this->Etat = $Etat;
        return $this;
    }

    public function getOrganisateurID(): ?int
    {
        return $this->Organisateur_ID;
    }

    public function setOrganisateurID(int $Organisateur_ID): static
    {
        $this->Organisateur_ID = $Organisateur_ID;
        return $this;
    }
}
