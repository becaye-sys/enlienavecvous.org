<?php


namespace App\DataFixtures;


use Cron\CronBundle\Entity\CronJob;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CronFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $cron = new CronJob();
        $cron->setName("test");
        $cron->setDescription("test");
        $cron->setCommand("bookings:clean:past");
        $cron->setSchedule("30 * * * *");
        $cron->setEnabled(true);
        $manager->persist($cron);
        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            AppointmentFixtures::class,
        );
    }
}