<?php

namespace App\DataFixtures;

use App\Entity\Patient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PatientFixtures extends Fixture implements DependentFixtureInterface
{
    public const PATIENT_USER_REFERENCE = "patient_user";

    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        if ($_SERVER['APP_ENV'] === 'dev') {
            $faker = Factory::create("fr");
        }
        for ($i = 1; $i <= 5; $i++) {
            $patient = new Patient();
            $patient->setEmail("patient$i@gmail.com");
            $patient->setPassword($this->encoder->encodePassword($patient, "password"));
            $patient->setEmailToken('');
            $patient->setIsActive(true);
            $patient->setFirstName($faker ?? $faker->firstName ?? "Firstname");
            $patient->setLastName($faker ?? $faker->lastName ?? "Lastname");
            $patient->setCountry("France");
            $patient->setZipCode($faker ?? $faker->postcode ?? "01500");
            $patient->setPhoneNumber($faker ?? $faker->phoneNumber ?? "0600000000");
            $patient->setHasAcceptedTermsAndPolicies(true);
            $this->addReference(self::PATIENT_USER_REFERENCE."_$i", $patient);
            $patient->setIsMajor(true);
            $manager->persist($patient);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            TownFixtures::class,
        );
    }
}
