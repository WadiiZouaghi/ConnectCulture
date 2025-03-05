<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    private ?string $phone = null;

    #[ORM\Column]
    private array $roles = ['ROLE_USER'];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    // Temporary field for plain password (not stored in the DB)
    private ?string $plainPassword = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'user')]
    private Collection $events;

    /**
     * @var Collection<int, Participation>
     */
    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: 'user')]
    private Collection $participations;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $google_id = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $resetToken = null;
    
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $banned = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $banReason = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $bannedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $resetTokenExpiresAt = null;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->roles = ['ROLE_USER'];
        $this->events = new ArrayCollection();
        $this->participations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getRoles(): array
    {
        if (!in_array('ROLE_USER', $this->roles, true)) {
            $this->roles[] = 'ROLE_USER';
        }
        return array_unique($this->roles);
    }

    public function setRoles(array $roles): static
    {
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }
        $this->roles = array_unique($roles);
        return $this;
    }

    public function addRole(string $role): static
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setUser($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getUser() === $this) {
                $event->setUser(null);
            }
        }

        return $this;
    }

    public function isOrganizer(): bool
    {
        return in_array('ROLE_ORGANIZER', $this->roles, true);
    }

    /**
     * @return Collection<int, Participation>
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(Participation $participation): static
    {
        if (!$this->participations->contains($participation)) {
            $this->participations->add($participation);
            $participation->setUser($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $participation): static
    {
        if ($this->participations->removeElement($participation)) {
            // set the owning side to null (unless already changed)
            if ($participation->getUser() === $this) {
                $participation->setUser(null);
            }
        }

        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->google_id;
    }

    public function setGoogleId(string $google_id): static
    {
        $this->google_id = $google_id;

        return $this;
    }
      // Add getter and setter
      public function getResetToken(): ?string
      {
          return $this->resetToken;
      }
      
      public function setResetToken(?string $resetToken): self
      {
          $this->resetToken = $resetToken;
          return $this;
      }
      
      // Add getter and setter
        public function isBanned(): bool
        {
            return $this->banned;
        }

    
        public function setBanned(bool $banned): self
        {
            $this->banned = $banned;
            if ($banned) {
                $this->bannedAt = new \DateTimeImmutable();
            } else {
                $this->bannedAt = null;
                $this->banReason = null;
            }
            return $this;
        }
    
        public function getBanReason(): ?string
        {
            return $this->banReason;
        }
    
        public function setBanReason(?string $banReason): self
        {
            $this->banReason = $banReason;
            return $this;
        }
    
        public function getBannedAt(): ?\DateTimeImmutable
        {
            return $this->bannedAt;
        }
    
        public function setBannedAt(?\DateTimeImmutable $bannedAt): self
        {
            $this->bannedAt = $bannedAt;
            return $this;
        }
        // Add these getter and setter methods
    public function getResetTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->resetTokenExpiresAt;
    }

    public function setResetTokenExpiresAt(?\DateTimeInterface $resetTokenExpiresAt): self
    {
        $this->resetTokenExpiresAt = $resetTokenExpiresAt;
        return $this;
}
}