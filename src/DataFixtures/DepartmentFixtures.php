<?php


namespace App\DataFixtures;


use App\Entity\Department;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class DepartmentFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        // load regions.json
        $departmentFile = file_get_contents(__DIR__."/../../departments.json");
        // parse regions.json to array
        $departmentArray = json_decode($departmentFile, true);
        // for each region -> create Region and persist
        foreach ($departmentArray as $value) {
            $department = new Department();
            $department->setName($value["nom"]);
            $department->setCode($value["code"]);
            $department->setCodeRegion($value["codeRegion"]);
            $manager->persist($department);
        }
        // flush
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['departments'];
    }
}