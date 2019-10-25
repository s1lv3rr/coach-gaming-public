<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InfoCoachRepository")
 */
class InfoCoach
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
     * @Groups({"detail-coach", "profil", "home"})
     */
    private $price;

    /**
     * @ORM\Column(type="text")
     * @Groups({"detail-coach", "profil"})
     */
    private $description;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"home", "detail-coach", "profil"})
     */
    private $rating;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail-coach", "profil"})
     */
    private $youtube;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail-coach", "profil"})
     */
    private $facebook;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail-coach", "profil"})
     */
    private $insta;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail-coach", "profil"})
     */
    private $twitch;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", mappedBy="infoCoach", cascade={"persist", "remove"})
     * @Groups({"home", "detail-coach"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="infoCoaches")
     *  @Groups({"home", "detail-coach"})
     */
    private $team;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game", inversedBy="infoCoaches")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"detail-coach"})
     */
    private $game;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Review", mappedBy="infoCoach")
     * @Groups({"detail-coach"})
     */
    private $reviews;
   
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Unavailability", mappedBy="infoCoach", orphanRemoval=true)
     */
    private $unavailabilities;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->availabilities = new ArrayCollection();
        $this->unavailabilities = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->description;
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getYoutube(): ?string
    {
        return $this->youtube;
    }

    public function setYoutube(?string $youtube): self
    {
        $this->youtube = $youtube;

        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): self
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getInsta(): ?string
    {
        return $this->insta;
    }

    public function setInsta(?string $insta): self
    {
        $this->insta = $insta;

        return $this;
    }

    public function getTwitch(): ?string
    {
        return $this->twitch;
    }

    public function setTwitch(?string $twitch): self
    {
        $this->twitch = $twitch;

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

        // set (or unset) the owning side of the relation if necessary
        $newInfoCoach = $user === null ? null : $this;
        if ($newInfoCoach !== $user->getInfoCoach()) {
            $user->setInfoCoach($newInfoCoach);
        }

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setInfoCoach($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->contains($review)) {
            $this->reviews->removeElement($review);
            // set the owning side to null (unless already changed)
            if ($review->getInfoCoach() === $this) {
                $review->setInfoCoach(null);
            }
        }

        return $this;
    }

    

    /**
     * @return Collection|Unavailability[]
     */
    public function getUnavailabilities(): Collection
    {
        return $this->unavailabilities;
    }

    public function addUnavailability(Unavailability $unavailability): self
    {
        if (!$this->unavailabilities->contains($unavailability)) {
            $this->unavailabilities[] = $unavailability;
            $unavailability->setInfoCoach($this);
        }

        return $this;
    }

    public function removeUnavailability(Unavailability $unavailability): self
    {
        if ($this->unavailabilities->contains($unavailability)) {
            $this->unavailabilities->removeElement($unavailability);
            // set the owning side to null (unless already changed)
            if ($unavailability->getInfoCoach() === $this) {
                $unavailability->setInfoCoach(null);
            }
        }

        return $this;
    }
}
