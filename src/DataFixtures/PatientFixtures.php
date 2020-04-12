<?php

namespace App\DataFixtures;

use App\Entity\Patient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PatientFixtures extends Fixture implements DependentFixtureInterface
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create("fr");
        for ($i = 1; $i <= 5; $i++) {
            $patient = new Patient();
            $patient->setEmail("patient$i@gmail.com");
            $patient->setPassword($this->encoder->encodePassword($patient, "password"));
            $patient->setEmailToken('');
            $patient->setIsActive(true);
            $patient->setFirstName($faker->firstName);
            $patient->setLastName($faker->lastName);
            $patient->setCountry("France");
            $patient->setZipCode($faker->postcode);
            $patient->setPhoneNumber($faker->phoneNumber);
            $patient->setHasAcceptedTermsAndPolicies(true);
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
