<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HistoryRepository")
 */
class History extends Appointment
{
    public const HISTORY_ACTIONS = [
        'SET_BOOKED' => "booked",
        'SET_CANCELLED' => "cancelled"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $action;

    /**
     * @ORM\Column(type="datetime")
     */
    private $actionedAt;

    public function __construct()
    {
        parent::__construct();
        $this->actionedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getActionedAt(): ?\DateTimeInterface
    {
        return $this->actionedAt;
    }

    public function setActionedAt(\DateTimeInterface $actionedAt): self
    {
        $this->actionedAt = $actionedAt;

        return $this;
    }
}
