<?php

namespace App\Entity;

use App\Interfaces\TherapistInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, TherapistInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="array")
     */
    protected $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isActive;

    /**
     * @ORM\Column(type="string")
     */
    protected $emailToken;

    /**
     * @ORM\Column(type="string")
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string")
     */
    protected $lastName;

    /**
     * @ORM\Column(type="string")
     */
    protected $country;

    /**
     * @ORM\Column(type="string")
     */
    protected $zipCode;

    /**
     * @ORM\Column(type="string")
     */
    protected $phoneNumber;

    /**
     * @ORM\Column(type="string")
     */
    private $ethicEntityCodeLabel;

    /**
     * @ORM\Column(type="string")
     */
    private $schoolEntityLabel;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $hasAcceptedTermsAndPolicies;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $hasCertification;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isSupervised;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isRespectingEthicalFrameWork;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
        $this->roles = ['ROLE_USER'];
        $this->isActive = false;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getEmailToken(): ?string
    {
        return $this->emailToken;
    }

    public function setEmailToken(string $emailToken): self
    {
        $this->emailToken = $emailToken;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getEthicEntityCodeLabel(): ?string
    {
        return $this->ethicEntityCodeLabel;
    }

    public function setEthicEntityCodeLabel(string $ethicEntityCodeLabel): void
    {
        $this->ethicEntityCodeLabel = $ethicEntityCodeLabel;
    }

    public function getSchoolEntityLabel(): ?string
    {
        return $this->schoolEntityLabel;
    }

    public function setSchoolEntityLabel(string $schoolEntityLabel): void
    {
        $this->schoolEntityLabel = $schoolEntityLabel;
    }

    public function getHasAcceptedTermsAndPolicies(): ?bool
    {
        return $this->hasAcceptedTermsAndPolicies;
    }

    public function setHasAcceptedTermsAndPolicies(bool $hasAcceptedTermsAndPolicies): void
    {
        $this->hasAcceptedTermsAndPolicies = $hasAcceptedTermsAndPolicies;
    }

    public function getHasCertification(): ?bool
    {
        return $this->hasCertification;
    }

    public function isOwningCertification(): ?bool
    {
        return $this->hasCertification;
    }

    public function setHasCertification(bool $hasCertification): void
    {
        $this->hasCertification = $hasCertification;
    }

    public function getIsSupervised(): ?bool
    {
        return $this->isSupervised;
    }

    public function isSupervised(): ?bool
    {
        return $this->isSupervised;
    }

    public function setIsSupervised(bool $isSupervised): void
    {
        $this->isSupervised = $isSupervised;
    }

    public function getIsRespectingEthicalFrameWork(): ?bool
    {
        return $this->isRespectingEthicalFrameWork;
    }

    public function setIsRespectingEthicalFrameWork(bool $isRespectingEthicalFrameWork): void
    {
        $this->isRespectingEthicalFrameWork = $isRespectingEthicalFrameWork;
    }

    public function isRespectingEthicalFrameWork(): ?bool
    {
        return $this->isRespectingEthicalFrameWork;
    }

    public function setUniqueEmailToken(): self
    {
        $this->emailToken = uniqid("", true);
        return $this;
    }
}
