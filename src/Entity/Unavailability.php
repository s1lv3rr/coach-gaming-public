<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UnavailabilityRepository")
 */
class Unavailability
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"unavailability"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"unavailability"})
     */
    private $start;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"unavailability"})
     */
    private $end;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $startUnix;

    /**
     * @ORM\Column(type="integer")
     */
    private $endUnix;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\InfoCoach", inversedBy="unavailabilities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $infoCoach;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="reservations")
     * @Groups({"unavailability"})
     */
    private $client; 
    
    public function __toString()
    {   
        if(null !== $this->client) {
            return $this->infoCoach->getUser()->getUsername();
        }

       return "Indisponibilité"; 
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getStartUnix(): ?int
    {
        return $this->startUnix;
    }

    public function setStartUnix(int $startUnix): self
    {
        $this->startUnix = $startUnix;

        return $this;
    }

    public function getEndUnix(): ?int
    {
        return $this->endUnix;
    }

    public function setEndUnix(int $endUnix): self
    {
        $this->endUnix = $endUnix;

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

    public function getInfoCoach(): ?InfoCoach
    {
        return $this->infoCoach;
    }

    public function setInfoCoach(?InfoCoach $infoCoach): self
    {
        $this->infoCoach = $infoCoach;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

        return $this;
    }
    
}
