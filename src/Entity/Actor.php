<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'actors')]
class Actor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'actor_id')]
    private $actor_id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $lastName;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $email;

    #[ORM\Column(type: 'string', length: 255)]
    private $password;

    #[ORM\Column(type: 'string', length: 50)]
    private $role;

    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'actors')]
    #[ORM\JoinTable(name: 'actor_group',
        joinColumns: [new ORM\JoinColumn(name: 'actor_id', referencedColumnName: 'actor_id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'id', referencedColumnName: 'id')]
    )]
    private $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function getActorId(): ?int { return $this->actor_id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(string $lastName): self { $this->lastName = $lastName; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }
    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }
    public function getRole(): ?string { return $this->role; }
    public function setRole(string $role): self { $this->role = $role; return $this; }
    public function getGroups(): Collection { return $this->groups; }
    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
            $group->addActor($this);
        }
        return $this;
    }
    public function removeGroup(Group $group): self
    {
        if ($this->groups->removeElement($group)) {
            $group->removeActor($this);
        }
        return $this;
    }
}