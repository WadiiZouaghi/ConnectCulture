<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "text")]
    private string $content;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $author = null;

    #[ORM\Column(type: "integer")]
    private int $userId;

    #[ORM\ManyToOne(targetEntity: Blog::class, inversedBy: "comments")]
    #[ORM\JoinColumn(nullable: false)]
    private Blog $blog;

    public function getId(): int
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getBlog(): Blog
    {
        return $this->blog;
    }

    public function setBlog(Blog $blog): self
    {
        $this->blog = $blog;
        return $this;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
