<?php


namespace App\DataFixtures;


use App\Entity\Appointment;
use App\Entity\History;
use App\Services\HistoryHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class HistoryFixtures extends Fixture implements DependentFixtureInterface
{
    public const HISTORY_REFERENCE = "history_";

    private $historyHelper;

    public function __construct(HistoryHelper $historyHelper)
    {
        $this->historyHelper = $historyHelper;
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 80; $i++) {
            if ($i%2 === 0) {
                /** @var Appointment $appointment */
                $appointment = $this->getReference(AppointmentFixtures::APPOINT_REFERENCE.$i);
                if ($appointment->getStatus() === Appointment::STATUS_BOOKED) {
                    $history = $this->historyHelper->addHistoryItem(History::ACTIONS[History::ACTION_BOOKED], $appointment);
                }
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            AppointmentFixtures::class
        ];
    }
}