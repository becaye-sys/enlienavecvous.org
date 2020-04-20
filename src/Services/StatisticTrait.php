<?php


namespace App\Services;


use App\Repository\AppointmentRepository;
use App\Repository\PatientRepository;
use App\Repository\TherapistRepository;

trait StatisticTrait
{
    private $therapistRepository;
    private $patientRepository;
    private $appointmentRepository;

    public function __construct(
        TherapistRepository $therapistRepository,
        PatientRepository $patientRepository,
        AppointmentRepository $appointmentRepository
    )
    {
        $this->therapistRepository = $therapistRepository;
        $this->patientRepository = $patientRepository;
        $this->appointmentRepository = $appointmentRepository;
    }

    public function getFunFacts(): array
    {
        $volunteers = sizeof($this->therapistRepository->findBy(['isActive' => true]));
        return [
            'happy_helped' => 0,
            'success_missions' => 0,
            'volunteer_reached' => $volunteers,
            'globalization_work' => 0
        ];
    }
}