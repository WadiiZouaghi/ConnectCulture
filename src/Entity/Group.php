<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_group')]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'id')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\ManyToMany(targetEntity: Actor::class, mappedBy: 'groups')]
    private Collection $actors;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $eventDate = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $maxParticipants = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $visibility = 'public';

    #[ORM\Column(type: 'blob', nullable: true)]
    private $coverPicture = null;

    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'group', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $posts;

    public function __construct()
    {
        $this->actors = new ArrayCollection();
        $this->posts = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getActors(): Collection { return $this->actors; }
    public function addActor(Actor $actor): self { if (!$this->actors->contains($actor)) { $this->actors->add($actor); $actor->addGroup($this); } return $this; }
    public function removeActor(Actor $actor): self { if ($this->actors->removeElement($actor)) { $actor->removeGroup($this); } return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function getLocation(): ?string { return $this->location; }
    public function setLocation(?string $location): self { $this->location = $location; return $this; }
    public function getEventDate(): ?\DateTimeInterface { return $this->eventDate; }
    public function setEventDate(?\DateTimeInterface $eventDate): self { $this->eventDate = $eventDate; return $this; }
    public function getMaxParticipants(): ?int { return $this->maxParticipants; }
    public function setMaxParticipants(?int $maxParticipants): self { $this->maxParticipants = $maxParticipants; return $this; }
    public function getVisibility(): string { return $this->visibility; }
    public function setVisibility(string $visibility): self { $this->visibility = $visibility; return $this; }
    public function isPublic(): bool { return $this->visibility === 'public'; }
    public function getCoverPicture() { return $this->coverPicture; }
    public function setCoverPicture($coverPicture): self { $this->coverPicture = $coverPicture; return $this; }
    public function getPosts(): Collection { return $this->posts; }
    public function addPost(Post $post): self { if (!$this->posts->contains($post)) { $this->posts->add($post); $post->setGroup($this); } return $this; }
    public function removePost(Post $post): self { if ($this->posts->removeElement($post)) { $post->setGroup(null); } return $this; }
    public function getPostCount(): int { return $this->posts->count(); }
    public function getMemberCount(): int { return $this->actors->count(); }
    public function isFull(): bool { return $this->maxParticipants !== null && $this->getMemberCount() >= $this->maxParticipants; }
    public function isMember(Actor $actor): bool { return $this->actors->contains($actor); }
}