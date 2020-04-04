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

        for ($i = 1; $i <= 50; $i++) {
            $refId = random_int(1,8);
            $appointment = new Appointment($this->getReference(TherapistFixtures::THERAPIST_USER_REFERENCE."_$refId"));
            $appointment->setLocation($faker->countryCode);
            $appointment->setBookingDate($faker->dateTimeBetween('now', '+2 months'));
            $randomDate = $this->getRandomDate();
            $appointment->setBookingStart(new \DateTime($randomDate['start']));
            $appointment->setBookingEnd(new \DateTime($randomDate['end']));
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
        $month = random_int(4,12);
        $hour = random_int(9,20);
        $endHour = $hour+1;
        $minute = random_int(0,59);
        $second = random_int(0,59);
        return [
            'date' => "$day/04/2020",
            'start' => "$hour:$minute:$second",
            'end' => "$endHour:$minute:$second"
        ];
    }
}