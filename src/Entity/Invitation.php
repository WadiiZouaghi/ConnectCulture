<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invitations')]
class Invitation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'invitation_id')]
    private ?int $invitationId = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'id', nullable: false)]
    private ?Group $group = null;

    #[ORM\ManyToOne(targetEntity: Actor::class)]
    #[ORM\JoinColumn(name: 'inviter_id', referencedColumnName: 'actor_id', nullable: true)]
    private ?Actor $inviter = null;

    #[ORM\ManyToOne(targetEntity: Actor::class)]
    #[ORM\JoinColumn(name: 'invitee_id', referencedColumnName: 'actor_id', nullable: false)]
    private ?Actor $invitee = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $status = 'pending';

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getInvitationId(): ?int
    {
        return $this->invitationId;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): self
    {
        $this->group = $group;
        return $this;
    }

    public function getInviter(): ?Actor
    {
        return $this->inviter;
    }

    public function setInviter(?Actor $inviter): self
    {
        $this->inviter = $inviter;
        return $this;
    }

    public function getInvitee(): ?Actor
    {
        return $this->invitee;
    }

    public function setInvitee(?Actor $invitee): self
    {
        $this->invitee = $invitee;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}