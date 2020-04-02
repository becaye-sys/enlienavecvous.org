<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PatientRepository")
 */
class Patient extends User
{
    private const ROLE_PATIENT = "ROLE_PATIENT";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isMajor;

    public function __construct()
    {
        parent::__construct();
        $this->roles = ["ROLE_USER", self::ROLE_PATIENT];
    }

    public function getId(): ?int
    {
        return parent::getId();
    }

    public function getIsMajor(): ?bool
    {
        return $this->isMajor;
    }

    public function setIsMajor(bool $isMajor): self
    {
        $this->isMajor = $isMajor;

        return $this;
    }
}
