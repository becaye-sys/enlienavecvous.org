<?php


namespace App\DataFixtures;


use App\Entity\Department;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Services\FixturesTrait;

class DepartmentFixtures extends Fixture implements FixtureGroupInterface
{
    use FixturesTrait;

    public const DEPARTMENT_FR_REFERENCE = "department_fr";
    public const DEPARTMENT_CH_REFERENCE = "department_ch";
    public const DEPARTMENT_LU_REFERENCE = "department_lu";
    public const DEPARTMENT_BE_REFERENCE = "department_be";

    public function load(ObjectManager $manager)
    {
        $this->loadFrenchDepartments($manager);
        $this->loadBelgiumDepartments($manager);
        $this->loadLuxembourgDepartments($manager);
        $this->loadSwissDepartments($manager);

        // flush
        $manager->flush();
    }

    private function loadFrenchDepartments(ObjectManager $manager)
    {
        $departmentArray = $this->getDecodedArrayFromFile(__DIR__ . "/../../public/data/departments_fr.json");
        // for each region -> create Region and persist
        foreach ($departmentArray as $value) {
            $department = new Department();
            $department->setCountry("fr");
            $department->setName($value["nom"]);
            $department->setCode($value["code"]);
            $this->addReference(self::DEPARTMENT_FR_REFERENCE . "_" . $value["code"], $department);
            $manager->persist($department);
        }
    }

    private function loadBelgiumDepartments(ObjectManager $manager)
    {
        $departmentArray = $this->getDecodedArrayFromFile(__DIR__ . "/../../public/data/departements_be.json");
        $faker = Factory::create("fr");
        // for each region -> create Region and persist
        foreach ($departmentArray as $value) {
            $department = new Department();
            $department->setCountry("be");
            $department->setName($value["dp"]);
            $department->setCode($value["cp"]);
            $this->addReference(self::DEPARTMENT_BE_REFERENCE . "_" . $value["cp"], $department);
            $manager->persist($department);
        }
    }

    private function loadLuxembourgDepartments(ObjectManager $manager)
    {
        $departmentArray = $this->getDecodedArrayFromFile(__DIR__ . "/../../public/data/departments_lu.json");
        // for each region -> create Region and persist
        foreach ($departmentArray as $value) {
            $department = new Department();
            $department->setCountry("lu");
            $department->setName($value["CANTON"]);
            $department->setCode($value["LAU2"]);
            $this->addReference(self::DEPARTMENT_LU_REFERENCE . "_" . $value["LAU2"], $department);
            $manager->persist($department);
        }
    }

    private function loadSwissDepartments(ObjectManager $manager)
    {
        $departmentArray = $this->getDecodedArrayFromFile(__DIR__ . "/../../public/data/departements_ch.json");
        // for each region -> create Region and persist
        foreach ($departmentArray as $value) {
            $department = new Department();
            $department->setCountry("ch");
            $department->setName($value["dp"]);
            $department->setCode($value["cp"]);
            $this->addReference(self::DEPARTMENT_CH_REFERENCE . "_" . $value["cp"], $department);
            $manager->persist($department);
        }
    }

    public static function getGroups(): array
    {
        return ['departments'];
    }
}