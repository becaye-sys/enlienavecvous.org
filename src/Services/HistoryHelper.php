<?php


namespace App\Services;


use App\Entity\Appointment;
use App\Entity\History;
use Doctrine\ORM\EntityManagerInterface;

class HistoryHelper
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addHistoryItem(Appointment $appointment, string $action)
    {
        $history = new History();
        $history->setAction($action);
        $history->setBooked($appointment->getBooked());
        $history->setBookingDate($appointment->getBookingDate());
        $history->setBookingStart($appointment->getBookingStart());
        $history->setBookingEnd($appointment->getBookingEnd());
        $history->setTherapist($appointment->getTherapist());
        $history->setPatient($appointment->getPatient());
        $history->setLocation($appointment->getLocation());
        $history->setCancelled($appointment->getCancelled());
        $history->setCancelMessage($appointment->getCancelMessage());
        $this->entityManager->persist($history);
    }
}