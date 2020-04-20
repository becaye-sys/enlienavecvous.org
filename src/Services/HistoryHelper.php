<?php


namespace App\Services;


use App\Entity\Appointment;
use App\Entity\BookingHistory;
use App\Entity\History;
use App\Entity\Patient;
use App\Entity\Therapist;
use Doctrine\ORM\EntityManagerInterface;

class HistoryHelper
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addHistoryItem(string $action, Appointment $appointment = null): History
    {
        $history = new History();
        $history->setAction($action);
        if ($action !== History::ACTIONS[History::ACTION_DELETED_BY_THERAPIST] && null !== $appointment) {
            $bookingHistory = $this->createBookingHistory($appointment);
            $bookingHistory->addHistory($history);
            $this->entityManager->persist($bookingHistory);
        }
        if (null !== $appointment) {
            if ($appointment->getTherapist() instanceof Therapist) {
                $history->setTherapist($appointment->getTherapist());
            }
            if ($appointment->getPatient() instanceof Patient) {
                $history->setPatient($appointment->getPatient());
            }
        }
        $this->entityManager->persist($history);
        return $history;
    }

    private function createBookingHistory(Appointment $appointment): BookingHistory
    {
        $bookingHistory = new BookingHistory();
        $bookingHistory->setBookingDate($appointment->getBookingDate());
        $bookingHistory->setBookingStart($appointment->getBookingStart());
        $bookingHistory->setStatus($appointment->getStatus());
        return $bookingHistory;
    }
}