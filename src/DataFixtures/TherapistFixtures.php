<?php


namespace App\DataFixtures;


use App\Entity\Therapist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class TherapistFixtures extends Fixture
{
    public const THERAPIST_USER_REFERENCE = "therapist_user";

    /** @var UserPasswordEncoderInterface $encoder */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create("fr");

        for ($i = 1; $i <= 8; $i ++) {
            $therapist = new Therapist();
            $therapist->setEthicEntityCodeLabel("ethic label code $i");
            $therapist->setSchoolEntityLabel("school label $i");
            $therapist->setHasCertification(true);
            $therapist->setIsSupervised(true);
            $therapist->setIsRespectingEthicalFrameWork(true);
            $therapist->setEmail("therapist$i@gmail.com");
            $therapist->setPassword($this->encoder->encodePassword($therapist, "password"));
            $therapist->setIsActive(true);
            $therapist->setFirstName($faker->firstName);
            $therapist->setLastName($faker->lastName);
            $therapist->setCountry("France");
            $therapist->setZipCode($faker->randomElement(['01500', '01430', '69000']));
            $therapist->setPhoneNumber($faker->phoneNumber);
            $therapist->setHasAcceptedTermsAndPolicies(true);
            $this->addReference(self::THERAPIST_USER_REFERENCE."_$i", $therapist);
            $manager->persist($therapist);
        }
        $manager->flush();
    }
}