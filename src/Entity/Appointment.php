<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AppointmentRepository")
 */
class Appointment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"create_booking"})
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"create_booking"})
     */
    private $booked;

    /**
     * @ORM\Column(type="date")
     * @Groups({"create_booking"})
     */
    private $bookingDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Therapist", inversedBy="appointments")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"create_booking"})
     */
    private $therapist;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Patient", inversedBy="appointments")
     */
    private $patient;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"create_booking"})
     */
    private $location;

    /**
     * @ORM\Column(type="time")
     * @Groups({"create_booking"})
     */
    private $bookingStart;

    /**
     * @ORM\Column(type="time")
     */
    private $bookingEnd;

    /**
     * @ORM\Column(type="boolean")
     */
    private $cancelled;

    public function __construct(Therapist $therapist)
    {
        $this->booked = false;
        $this->therapist = $therapist;
        $this->cancelled = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooked(): ?bool
    {
        return $this->booked;
    }

    public function setBooked(bool $booked): self
    {
        $this->booked = $booked;

        return $this;
    }

    public function getBookingDate(): ?\DateTimeInterface
    {
        return $this->bookingDate;
    }

    public function setBookingDate(\DateTimeInterface $bookingDate): self
    {
        $this->bookingDate = $bookingDate;

        return $this;
    }

    public function getTherapist(): ?Therapist
    {
        return $this->therapist;
    }

    public function setTherapist(?Therapist $therapist): self
    {
        $this->therapist = $therapist;

        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getBookingStart(): ?\DateTimeInterface
    {
        return $this->bookingStart;
    }

    public function setBookingStart(\DateTimeInterface $bookingStart): self
    {
        $this->bookingStart = $bookingStart;

        return $this;
    }

    public function getBookingEnd(): ?\DateTimeInterface
    {
        return $this->bookingEnd;
    }

    public function setBookingEnd(\DateTimeInterface $bookingEnd): self
    {
        $this->bookingEnd = $bookingEnd;

        return $this;
    }

    public function getCancelled(): ?bool
    {
        return $this->cancelled;
    }

    public function setCancelled(bool $cancelled): self
    {
        $this->cancelled = $cancelled;

        return $this;
    }
}
