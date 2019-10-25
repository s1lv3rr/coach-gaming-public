<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReviewRepository")
 */
class Review
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"home", "detail-coach", "profil"})
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"home", "detail-coach"})
     * @Assert\LessThanOrEqual(5)
     * @Assert\NotNull
     */
    private $rating;

    /**
     * @ORM\Column(type="text")
     * @Groups({"home", "detail-coach"})
     * @Assert\NotBlank
     * @Assert\Length(min=10)
     * @Assert\Length(max=1000)
     */
    private $comment;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"detail-coach"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="reviewsPosted")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"home", "detail-coach"})     
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\InfoCoach", inversedBy="reviews")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"home", "detail-coach"})
     */
    private $infoCoach;

    public function __toString()
    {
        return $this->comment;
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
    
    public function getInfoCoach(): ?InfoCoach
    {
        return $this->infoCoach;
    }

    public function setInfoCoach(?InfoCoach $infoCoach): self
    {
        $this->infoCoach = $infoCoach;

        return $this;
    }
    
}
