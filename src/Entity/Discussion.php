<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\DiscussionRepository::class)]
#[ORM\Table(name: "discussions")]
class Discussion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "text")]
    private ?string $content = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: "discussions")]
    #[ORM\JoinColumn(name: "group_id", referencedColumnName: "id", nullable: false)]
    private ?Group $group = null;

    #[ORM\ManyToOne(targetEntity: Actor::class, inversedBy: "discussions")]
    #[ORM\JoinColumn(name: "actor_id", referencedColumnName: "id", nullable: true)]
    private ?Actor $author = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
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

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): self
    {
        $this->group = $group;
        return $this;
    }

    public function getAuthor(): ?Actor
    {
        return $this->author;
    }

    public function setAuthor(?Actor $author): self
    {
        $this->author = $author;
        return $this;
    }
}