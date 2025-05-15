<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
#[ORM\Entity(repositoryClass: CommentRepository::class)]

class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    #[ORM\Column(type: "text")]

    private $content;

    #[ORM\Column(type: "string", length: 255)]

    private $author;

    /**
     * @ORM\Column(type="integer")
     */
    #[ORM\Column(type: "integer")]

    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Blog", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    #[ORM\ManyToOne(targetEntity: Blog::class, inversedBy: "comments")]
    #[ORM\JoinColumn(nullable: false)]

    private $blog;

    // In Comment.php
    public function __construct(string $content, string $author, int $userId, Blog $blog)
    {
        $this->content = $content;
        $this->author = $author;
        $this->userId = $userId;
        $this->blog = $blog; // Make sure Blog is passed and set correctly
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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getBlog(): ?Blog
    {
        return $this->blog;
    }

    public function setBlog(Blog $blog): self
    {
        $this->blog = $blog;
        return $this;
    }
}
