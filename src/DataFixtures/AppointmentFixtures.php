<?php


namespace App\DataFixtures;


use App\Entity\Appointment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppointmentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create("fr");

        for ($i = 1; $i <= 80; $i++) {
            $refId = random_int(1,8);
            $appointment = new Appointment($this->getReference(TherapistFixtures::THERAPIST_USER_REFERENCE."_$refId"));
            $appointment->setLocation($faker->countryCode);
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
        $day = random_int(1,31);
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