<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameRepository")
 */
class Game
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"home", "detail-coach", "profil"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"home", "detail-coach"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100) 
     */
    private $editor;

    /**
     * @ORM\Column(type="text")
     * @Groups({"home"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"home"})
     */
    private $headerBackground;

    /**
     * @ORM\Column(type="datetime")
     */
    private $releaseDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"home"})
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\InfoCoach", mappedBy="game")
     * @Groups({"home"})      
     */
    private $infoCoaches;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Platform", inversedBy="games")
     */
    private $platforms;
       
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

      /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    public function __toString()
    {
        return $this->name;
    }

    public function __construct()
    {
        $this->infoCoaches = new ArrayCollection();
        $this->platforms = new ArrayCollection();
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

    public function getEditor(): ?string
    {
        return $this->editor;
    }

    public function setEditor(string $editor): self
    {
        $this->editor = $editor;

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

    public function getHeaderBackground(): ?string
    {
        return $this->headerBackground;
    }

    public function setHeaderBackground(string $headerBackground): self
    {
        $this->headerBackground = $headerBackground;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(\DateTimeInterface $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|InfoCoach[]
     */
    public function getInfoCoaches(): Collection
    {
        return $this->infoCoaches;
    }

    public function addInfoCoach(InfoCoach $infoCoach): self
    {
        if (!$this->infoCoaches->contains($infoCoach)) {
            $this->infoCoaches[] = $infoCoach;
            $infoCoach->setGame($this);
        }

        return $this;
    }

    public function removeInfoCoach(InfoCoach $infoCoach): self
    {
        if ($this->infoCoaches->contains($infoCoach)) {
            $this->infoCoaches->removeElement($infoCoach);
            // set the owning side to null (unless already changed)
            if ($infoCoach->getGame() === $this) {
                $infoCoach->setGame(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Platform[]
     */
    public function getPlatforms(): Collection
    {
        return $this->platforms;
    }

    public function addPlatform(Platform $platform): self
    {
        if (!$this->platforms->contains($platform)) {
            $this->platforms[] = $platform;
        }

        return $this;
    }

    public function removePlatform(Platform $platform): self
    {
        if ($this->platforms->contains($platform)) {
            $this->platforms->removeElement($platform);
        }

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

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    } 
   
}
