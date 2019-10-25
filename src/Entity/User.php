<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;



/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="app_user")
 * @UniqueEntity(fields="username", message="Nom d'utilisateur déjà pris, veuillez en choisir un autre")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"home", "detail-coach", "profil", "auth", "message", "unavailability"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"profil"})
     * @Assert\NotBlank
     * @Assert\Length(min=3)
     * @Assert\Length(max=50)
     *  @Groups({"unavailability", "profil"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"profil"})
     * @Assert\NotBlank
     * @Assert\Length(min=3)
     * @Assert\Length(max=50)
     * @Groups({"unavailability", "profil"})
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"home", "detail-coach", "profil", "message", "availability"})
     * @Assert\NotBlank
     * @Assert\Length(min=3)
     * @Assert\Length(max=50)
     */
    private $username;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"profil"})
     * @Assert\NotBlank
     * @Assert\Positive
     * @Assert\LessThanOrEqual(120)
     * @Groups({"unavailability", "profil"})
     */
    private $age;
        
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"home", "profil", "detail-coach"})    
     */
    private $avatar;
   
    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank
     * @Assert\Length(min=5)
     * @Assert\Length(max=100)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"profil"})
     * @Assert\NotBlank
     * @Assert\Email()
     * @Assert\Length(max=255)
     * @Groups({"unavailability", "profil"})
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"home"})
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Role", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $role;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\InfoCoach", inversedBy="user", cascade={"persist", "remove"})
     * @Groups({"profil"})
     */
    private $infoCoach;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Record", mappedBy="user", orphanRemoval=true)
     * @Groups({"detail-coach"})
     */
    private $records;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="sender")
     */
    private $sendedMessages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="receiver")
     */
    private $receivedMessages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Review", mappedBy="user", orphanRemoval=true)
     */
    private $reviewsPosted;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Unavailability", mappedBy="client")
     */
    private $reservations;
        
     
    public function __construct()
    {
        $this->records = new ArrayCollection();
        $this->sendedMessages = new ArrayCollection();
        $this->receivedMessages = new ArrayCollection();
        $this->reviewsPosted = new ArrayCollection();
        $this->reviewsReceived = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->reservations = new ArrayCollection();        
    }

    public function __toString()
    {
        return $this->username;
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

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;

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

    /**
     * @return Collection|Record[]
     */
    public function getRecords(): Collection
    {
        return $this->records;
    }

    public function addRecord(Record $record): self
    {
        if (!$this->records->contains($record)) {
            $this->records[] = $record;
            $record->setUser($this);
        }

        return $this;
    }

    public function removeRecord(Record $record): self
    {
        if ($this->records->contains($record)) {
            $this->records->removeElement($record);
            // set the owning side to null (unless already changed)
            if ($record->getUser() === $this) {
                $record->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getSendedMessages(): Collection
    {
        return $this->sendedMessages;
    }

    public function addSendedMessage(Message $sendedMessage): self
    {
        if (!$this->sendedMessages->contains($sendedMessage)) {
            $this->sendedMessages[] = $sendedMessage;
            $sendedMessage->setSender($this);
        }

        return $this;
    }

    public function removeSendedMessage(Message $sendedMessage): self
    {
        if ($this->sendedMessages->contains($sendedMessage)) {
            $this->sendedMessages->removeElement($sendedMessage);
            // set the owning side to null (unless already changed)
            if ($sendedMessage->getSender() === $this) {
                $sendedMessage->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getReceivedMessages(): Collection
    {
        return $this->receivedMessages;
    }

    public function addReceivedMessage(Message $receivedMessage): self
    {
        if (!$this->receivedMessages->contains($receivedMessage)) {
            $this->receivedMessages[] = $receivedMessage;
            $receivedMessage->setReceiver($this);
        }

        return $this;
    }

    public function removeReceivedMessage(Message $receivedMessage): self
    {
        if ($this->receivedMessages->contains($receivedMessage)) {
            $this->receivedMessages->removeElement($receivedMessage);
            // set the owning side to null (unless already changed)
            if ($receivedMessage->getReceiver() === $this) {
                $receivedMessage->setReceiver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviewsPosted(): Collection
    {
        return $this->reviewsPosted;
    }

    public function addReviewsPosted(Review $reviewsPosted): self
    {
        if (!$this->reviewsPosted->contains($reviewsPosted)) {
            $this->reviewsPosted[] = $reviewsPosted;
            $reviewsPosted->setUser($this);
        }

        return $this;
    }

    public function removeReviewsPosted(Review $reviewsPosted): self
    {
        if ($this->reviewsPosted->contains($reviewsPosted)) {
            $this->reviewsPosted->removeElement($reviewsPosted);
            // set the owning side to null (unless already changed)
            if ($reviewsPosted->getUser() === $this) {
                $reviewsPosted->setUser(null);
            }
        }

        return $this;
    }
        

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ]);
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized, ['allowed_classes' => false]);
    }

    public function eraseCredentials()
    {
    }

    public function getRoles(): array // fonction necessaire pour security.yml => ceci n'est pas un getter en relation avec ma BDD
    {
        $roles = [];

        if(!is_null($this->role)){

            $roles[] = $this->role->getCode(); // ici on stockera le code associé dans la BDD dans le genre ROLE_USER, ROLE_ADMIN, ROLE_MEMBER etc ...

        } else {
            $roles[] = 'ROLE_USER'; // par defaut si notre utilisateur a été stocké dans role on retournera role_user pour que symfony ne plante pas
        }

        return $roles;
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    

    /**
     * @return Collection|Unavailability[]
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Unavailability $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setClient($this);
        }

        return $this;
    }

    public function removeReservation(Unavailability $reservation): self
    {
        if ($this->reservations->contains($reservation)) {
            $this->reservations->removeElement($reservation);
            // set the owning side to null (unless already changed)
            if ($reservation->getClient() === $this) {
                $reservation->setClient(null);
            }
        }

        return $this;
    }
   
}
