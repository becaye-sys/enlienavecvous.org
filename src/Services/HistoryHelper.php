<?php


namespace App\Services;


use App\Entity\Appointment;
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
        if ($action !== History::ACTIONS[History::ACTION_DELETED_BY_THERAPIST]) {
            $history->setAppointment($appointment);
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
}