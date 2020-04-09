<?php


namespace App\DataFixtures;


use App\Entity\Department;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class DepartmentFixtures extends Fixture implements FixtureGroupInterface
{
    public const DEPARTMENT_FR_REFERENCE = "department_fr";
    public const DEPARTMENT_CH_REFERENCE = "department_ch";
    public const DEPARTMENT_LU_REFERENCE = "department_lu";
    public const DEPARTMENT_BE_REFERENCE = "department_be";

    public function load(ObjectManager $manager)
    {
        $this->loadFrenchRegions($manager);
        $this->loadLuxembourgRegions($manager);
        $this->loadBelgiumRegions($manager);
        $this->loadSwissRegions($manager);

        // flush
        $manager->flush();
    }

    private function loadFrenchRegions(ObjectManager $manager)
    {
        $departmentArray = $this->getDecodedArrayFromFile(__DIR__ . "/../../departments_fr.json");
        // for each region -> create Region and persist
        foreach ($departmentArray as $value) {
            $department = new Department();
            $department->setName($value["nom"]);
            $department->setCode($value["code"]);
            $department->setCodeRegion($value["codeRegion"]);
            $this->addReference(self::DEPARTMENT_FR_REFERENCE . "_" . $value["code"], $department);
            $manager->persist($department);
        }
    }

    private function loadSwissRegions(ObjectManager $manager)
    {
        $departmentArray = $this->getDecodedArrayFromFile(__DIR__ . "/../../departements_ch.json");
        // for each region -> create Region and persist
        foreach ($departmentArray as $value) {
            $department = new Department();
            $department->setName($value["dp"]);
            $department->setCode($value["cp"]);
            $department->setCodeRegion($value["cp"]);
            $this->addReference(self::DEPARTMENT_CH_REFERENCE . "_" . $value["cp"], $department);
            $manager->persist($department);
        }
    }

    private function loadLuxembourgRegions(ObjectManager $manager)
    {
        $departmentArray = $this->getDecodedArrayFromFile(__DIR__ . "/../../communes_lu.json");
        // for each region -> create Region and persist
        foreach ($departmentArray as $value) {
            $department = new Department();
            $department->setName($value["CANTON"]);
            $department->setCode(substr($value["LAU2"], 0, 1));
            $this->addReference(self::DEPARTMENT_LU_REFERENCE . "_" . $value["LAU2"], $department);
            $manager->persist($department);
        }
    }

    private function loadBelgiumRegions(ObjectManager $manager)
    {
        $departmentArray = $this->getDecodedArrayFromFile(__DIR__ . "/../../departements_be.json");
        $faker = Factory::create("fr");
        // for each region -> create Region and persist
        foreach ($departmentArray as $value) {
            $department = new Department();
            $department->setName($value["dp"]);
            $department->setCode($value["cp"]);
            $this->addReference(self::DEPARTMENT_BE_REFERENCE . "_" . $value["cp"], $department);
            $manager->persist($department);
        }
    }

    private function getDecodedArrayFromFile(string $file): array
    {
        $file = file_get_contents($file);
        // parse regions_fr.json to array
        $array = json_decode($file, true);
        return $array;
    }

    public static function getGroups(): array
    {
        return ['departments'];
    }
}