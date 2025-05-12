<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'full_name', type: 'string', length: 100)]
    private ?string $fullName = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private ?string $email = null;

    #[ORM\Column(name: 'phone_number', type: 'string', length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(name: 'role', type: 'string', length: 20, nullable: true)]
    private ?string $roles = '["ROLE_USER"]';

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $password = null;
    
    #[ORM\Column(name: 'is_banned', type: 'boolean', nullable: true, options: ['default' => 0])]
    private ?bool $isBanned = false;
    
    #[ORM\Column(name: 'ban_reason', type: 'text', nullable: true, columnDefinition: 'TEXT DEFAULT NULL')]
    private ?string $banReason = null;
    
    #[ORM\Column(type: 'string', length: 100)]
    private ?string $username = '';
    
    #[ORM\Column(name: 'created_at', type: 'datetime', columnDefinition: 'DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL')]
    private ?\DateTimeInterface $createdAt = null;

    // Temporary field for plain password (not stored in the DB)
    private ?string $plainPassword = null;

    /**
     * @var Collection<int, Event>
     */
    private Collection $events;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->roles = '["ROLE_USER"]';
        $this->events = new ArrayCollection();
        $this->username = '';
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

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getRoles(): array
    {
        // If the role is ADMIN, return both ROLE_ADMIN and ROLE_USER
        if ($this->roles === 'ADMIN') {
            return ['ROLE_ADMIN', 'ROLE_USER'];
        }
        
        // Otherwise, try to decode JSON if it looks like JSON, or use the role directly
        if (str_starts_with($this->roles ?? '', '[')) {
            $rolesArray = json_decode($this->roles, true) ?? ['ROLE_USER'];
        } else {
            // Convert simple string role to Symfony role format
            $rolesArray = ['ROLE_' . strtoupper($this->roles ?? 'USER')];
        }
        
        // Always include ROLE_USER
        if (!in_array('ROLE_USER', $rolesArray, true)) {
            $rolesArray[] = 'ROLE_USER';
        }
        
        return array_unique($rolesArray);
    }

    public function setRoles(array $roles): static
    {
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }
        
        // If the array contains ROLE_ADMIN, set the role to ADMIN
        if (in_array('ROLE_ADMIN', $roles, true)) {
            $this->roles = 'ADMIN';
        } else {
            // Otherwise, set it to USER
            $this->roles = 'USER';
        }
        
        return $this;
    }

    public function getPassword(): ?string
    {
        // If the password is not hashed (less than 30 chars or doesn't start with $2y$)
        // and is not a SHA256 hash (which is 64 chars)
        if ($this->password && strlen($this->password) < 30 && !str_starts_with($this->password, '$2y$') && strlen($this->password) !== 64) {
            return 'PLAIN_' . $this->password;
        }
        
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }
    
    public function getIsBanned(): ?bool
    {
        return $this->isBanned;
    }
    
    public function setIsBanned(?bool $isBanned): static
    {
        $this->isBanned = $isBanned;
        return $this;
    }
    
    public function getBanReason(): ?string
    {
        return $this->banReason;
    }
    
    public function setBanReason(?string $banReason): static
    {
        $this->banReason = $banReason;
        return $this;
    }
    
    public function getUsername(): ?string
    {
        return $this->username;
    }
    
    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
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
        $this->plainPassword = null;
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
} 