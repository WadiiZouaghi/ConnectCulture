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
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private ?string $location = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $eventDate = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $maxParticipants = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $visibility = null;

    #[ORM\Column(type: 'blob', nullable: false)]
    private $coverPicture = null;

    #[ORM\Column(type: 'float', nullable: false)]
    private ?float $latitude = null;

    #[ORM\Column(type: 'float', nullable: false)]
    private ?float $longitude = null;

    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'group', cascade: ['persist', 'remove'])]
    private Collection $posts;

    #[ORM\OneToMany(targetEntity: Discussion::class, mappedBy: 'group', cascade: ['persist', 'remove'])]
    private Collection $discussions;

    #[ORM\ManyToMany(targetEntity: Actor::class, mappedBy: 'groups')]
    private Collection $actors;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->discussions = new ArrayCollection();
        $this->actors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getEventDate(): ?\DateTimeInterface
    {
        return $this->eventDate;
    }

    public function setEventDate(?\DateTimeInterface $eventDate): self
    {
        $this->eventDate = $eventDate;
        return $this;
    }

    public function getMaxParticipants(): ?int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(?int $maxParticipants): self
    {
        $this->maxParticipants = $maxParticipants;
        return $this;
    }

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function getCoverPicture()
    {
        return $this->coverPicture;
    }

    public function setCoverPicture($coverPicture): self
    {
        $this->coverPicture = $coverPicture;
        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setGroup($this);
        }
        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            if ($post->getGroup() === $this) {
                $post->setGroup(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Discussion>
     */
    public function getDiscussions(): Collection
    {
        return $this->discussions;
    }

    public function addDiscussion(Discussion $discussion): self
    {
        if (!$this->discussions->contains($discussion)) {
            $this->discussions->add($discussion);
            $discussion->setGroup($this);
        }
        return $this;
    }

    public function removeDiscussion(Discussion $discussion): self
    {
        if ($this->discussions->removeElement($discussion)) {
            if ($discussion->getGroup() === $this) {
                $discussion->setGroup(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Actor>
     */
    public function getActors(): Collection
    {
        return $this->actors;
    }

    public function addActor(Actor $actor): self
    {
        if (!$this->actors->contains($actor)) {
            $this->actors->add($actor);
            $actor->addGroup($this);
        }
        return $this;
    }

    public function removeActor(Actor $actor): self
    {
        if ($this->actors->removeElement($actor)) {
            $actor->removeGroup($this);
        }
        return $this;
    }

    public function isMember(?Actor $actor): bool
    {
        if (!$actor) {
            return false;
        }
        return $this->actors->contains($actor);
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }
}