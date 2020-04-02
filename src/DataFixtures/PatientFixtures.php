<?php

namespace App\DataFixtures;

use App\Entity\Patient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PatientFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 5; $i++) {
            $patient = new Patient();
            $patient->setEmail("pat$i@pat.fr")
                ->setFirstName("pat$i");
            $patient->setLastName("pat$i");
            $patient->setPassword($this->encoder->encodePassword($patient, "pat$i"));
            $patient->setCountry("France");
            $patient->setZipCode("01");
            $patient->setPhoneNumber("01");
            $patient->setHasAcceptedTermsAndPolicies(true);
            $patient->setIsMajor(true);
            $manager->persist($patient);
        }

        $manager->flush();
    }
}
