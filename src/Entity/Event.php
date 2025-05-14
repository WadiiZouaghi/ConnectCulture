<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert; // Import validation constraints


#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: 'Name must be at least {{ limit }} characters long',
        maxMessage: 'Name cannot be longer than {{ limit }} characters'
    )]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true, columnDefinition: 'VARCHAR(255) DEFAULT NULL')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Destination must be at least {{ limit }} characters long',
        maxMessage: 'Destination cannot be longer than {{ limit }} characters'
    )]
    private ?string $destination = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true, columnDefinition: 'VARCHAR(50) DEFAULT NULL')]
    #[Assert\Choice(
        choices: ['sport', 'cultural', 'educational', 'entertainment', 'other'],
        message: 'Please select a valid event type'
    )]
    private ?string $eventtype = null;

    #[ORM\Column(type: 'text', nullable: true, columnDefinition: 'TEXT DEFAULT NULL')]
    #[Assert\Length(
        max: 1000,
        maxMessage: 'Description cannot be longer than {{ limit }} characters'
    )]
    private ?string $Description = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true, columnDefinition: 'VARCHAR(255) DEFAULT NULL')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Equipment list cannot be longer than {{ limit }} characters'
    )]
    private ?string $equipment = null;

    #[ORM\Column(name: 'user_id', type: 'integer', columnDefinition: 'INT NOT NULL DEFAULT 1')]
    private ?int $userId = 1;

    #[ORM\Column(type: 'string', length: 255, nullable: true, columnDefinition: 'VARCHAR(255) DEFAULT NULL')]
    private ?string $image = null;
    
    #[ORM\Column(type: 'date', nullable: true, columnDefinition: 'DATE DEFAULT NULL')]
    private ?\DateTimeInterface $date = null;
    
    #[ORM\Column(name: 'nbplaces', type: 'integer', nullable: true, columnDefinition: 'INT DEFAULT 0')]
    private ?int $nbPlaces = 0;
    
    /**
     * @var User|null
     */
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(?string $destination): static
    {
        $this->destination = $destination;

        return $this;
    }

    public function getEventtype(): ?string
    {
        return $this->eventtype;
    }

    public function setEventtype(?string $eventtype): static
    {
        $this->eventtype = $eventtype;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): static
    {
        $this->Description = $Description;

        return $this;
    }

    public function getEquipment(): ?string
    {
        return $this->equipment;
    }

    public function setEquipment(?string $equipment): static
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }
    
    /**
     * Get the associated User entity (not stored in database)
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the associated User entity (not stored in database)
     */
    public function setUser(?User $user): static
    {
        $this->user = $user;
        if ($user !== null) {
            $this->userId = $user->getId();
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }
    
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }
    
    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;
        
        return $this;
    }
    
    public function getNbPlaces(): ?int
    {
        return $this->nbPlaces;
    }
    
    public function setNbPlaces(?int $nbPlaces): static
    {
        $this->nbPlaces = $nbPlaces;
        
        return $this;
    }
}
