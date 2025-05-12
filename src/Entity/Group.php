<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`groups`')]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Name is required')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Name must be at least {{ limit }} characters long',
        maxMessage: 'Name cannot be longer than {{ limit }} characters'
    )]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Category cannot be longer than {{ limit }} characters'
    )]
    private ?string $category = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Type cannot be longer than {{ limit }} characters'
    )]
    private ?string $type = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Assert\Length(
        max: 100,
        maxMessage: 'City cannot be longer than {{ limit }} characters'
    )]
    private ?string $city = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\PositiveOrZero(message: 'Size must be a positive number or zero')]
    private ?int $size = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: 'Description cannot be longer than {{ limit }} characters'
    )]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Assert\Choice(
        choices: ['PUBLIC', 'PRIVATE', 'RESTRICTED'],
        message: 'Please select a valid visibility option'
    )]
    private ?string $visibility = null;

    #[ORM\Column(name: 'created_by_user_id', type: 'integer')]
    private ?int $createdByUserId = null;

    /**
     * @var User|null
     */
    private ?User $createdByUser = null;

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

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(?string $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getCreatedByUserId(): ?int
    {
        return $this->createdByUserId;
    }

    public function setCreatedByUserId(int $createdByUserId): static
    {
        $this->createdByUserId = $createdByUserId;

        return $this;
    }

    /**
     * Get the associated User entity (not stored in database)
     */
    public function getCreatedByUser(): ?User
    {
        return $this->createdByUser;
    }

    /**
     * Set the associated User entity (not stored in database)
     */
    public function setCreatedByUser(?User $user): static
    {
        $this->createdByUser = $user;
        if ($user !== null) {
            $this->createdByUserId = $user->getId();
        }

        return $this;
    }
}