<?php


namespace App\DataFixtures;


use App\Entity\Appointment;
use App\Entity\Patient;
use App\Entity\Therapist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppointmentFixtures extends Fixture implements DependentFixtureInterface
{
    public const APPOINT_REFERENCE = "appoint_";
    public function load(ObjectManager $manager)
    {
        if ($_SERVER['APP_ENV'] === 'dev') {
            $faker = Factory::create("fr");
        }

        for ($i = 1; $i <= 80; $i++) {
            $therapistId = random_int(1,8);
            /** @var Therapist $therapist */
            $therapist = $this->getReference(TherapistFixtures::THERAPIST_USER_REFERENCE."_$therapistId");

            $patientId = random_int(1,5);
            /** @var Patient $patient */
            $patient = $this->getReference(PatientFixtures::PATIENT_USER_REFERENCE."_$patientId");
            $appointment = new Appointment();
            $appointment->setTherapist($therapist);
            if ($i%2 === 0) {
                $appointment->setPatient($patient);
                $this->addReference(self::APPOINT_REFERENCE.$i, $appointment);
                $appointment->setStatus(Appointment::STATUS_BOOKED);
            } else {
                $appointment->setStatus(Appointment::STATUS_AVAILABLE);
            }
            $appointment->setLocation($faker ?? $faker->city ?? "Lyon");
            $randomDate = $this->getRandomDate();
            $date = $randomDate['start'];
            $start = new \DateTime($date);
            $interval = new \DateInterval('PT1H');
            $end = $start->add($interval);
            $appointment->setBookingDate($start);
            $appointment->setBookingStart($start);
            $appointment->setBookingEnd($end);
            $manager->persist($appointment);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TherapistFixtures::class,
        ];
    }

    private function getRandomDate(): array
    {
        $day = random_int(16,31);
        $hour = random_int(9,20);
        $minute = random_int(0,59);
        if ($day < 10) {
            $day = "0$day";
        }
        if ($hour < 10) {
            $hour = "0$hour";
        }
        if ($minute < 10) {
            $minute = "0$minute";
        }
        return [
            'start' => "$day-04-2020 $hour:$minute:00",
        ];
    }
}