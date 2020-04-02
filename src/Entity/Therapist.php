<?php

namespace App\Entity;

use App\Interfaces\TherapistInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TherapistRepository")
 */
class Therapist extends User implements TherapistInterface
{
    private const ROLE_THERAPIST = "ROLE_THERAPIST";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

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
        parent::__construct();
        $this->roles = ["ROLE_USER", self::ROLE_THERAPIST];
    }

    public function getId(): ?int
    {
        return $this->id;
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
}
