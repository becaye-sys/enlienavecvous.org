<?php


namespace App\DataFixtures;


use App\Entity\Department;
use App\Entity\Town;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Services\FixturesTrait;

class TownFixtures extends Fixture implements FixtureGroupInterface
{
    use FixturesTrait;

    public const TOWN_FR_REFERENCE = "town_fr";
    public const TOWN_CH_REFERENCE = "town_ch";
    public const TOWN_LU_REFERENCE = "town_lu";
    public const TOWN_BE_REFERENCE = "town_be";

    public function load(ObjectManager $manager)
    {
        $this->loadFrenchTowns($manager);
        $this->loadLuxembourgTowns($manager);
        $this->loadSwissTowns($manager);

        $manager->flush();
    }

    private function loadFrenchTowns(ObjectManager $manager)
    {
        $townsArray = $this->getDecodedArrayFromFile(__DIR__ . "/../../public/data/communes_fr.json");
        // for each region -> create Region and persist
        foreach ($townsArray as $key => $item) {
            $town = new Town();
            if (array_key_exists("nom", $item)) {
                $town->setName($item["nom"]);
            }
            if (array_key_exists("code", $item)) {
                $town->setCode($item["code"]);
            }
            if (array_key_exists("codeDepartement", $item)) {
                /** @var Department $department */
                $department = $this->getReference(DepartmentFixtures::DEPARTMENT_FR_REFERENCE."_".$item["codeDepartement"]);
                $town->setDepartment($department);
                $town->setScalarDepart($item["code"]);
            } else {
                $town->setScalarDepart($item["code"]);
            }
            if (array_key_exists("codesPostaux", $item)) {
                $town->setZipCodes($item["codesPostaux"]);
            }
            $this->addReference(self::TOWN_FR_REFERENCE . "_" . $item["code"], $town);
            $manager->persist($town);
        }
    }

    private function loadLuxembourgTowns(ObjectManager $manager)
    {
        $townsArray = $this->getDecodedArrayFromFile(__DIR__ . "/../../public/data/communes_lu.json");
        // for each region -> create Region and persist
        foreach ($townsArray as $key => $item) {
            $town = new Town();
            if (array_key_exists("COMMUNE", $item)) {
                $town->setName($item["COMMUNE"]);
            }
            if (array_key_exists("LAU2", $item)) {
                /** @var Department $department */
                $department = $this->getReference(DepartmentFixtures::DEPARTMENT_LU_REFERENCE."_".substr($item["LAU2"], 0, 2));
                $town->setDepartment($department);
                $town->setScalarDepart($item["CANTON"]);
                $town->setCode($item["LAU2"]);
            }
            if (array_key_exists("LAU2", $item)) {
                $town->setZipCodes([$item["LAU2"]]);
            }
            $this->addReference(self::TOWN_LU_REFERENCE . "_" . $item["LAU2"], $town);
            $manager->persist($town);
        }
    }

    private function loadSwissTowns(ObjectManager $manager)
    {
        $townsArray = $this->getDecodedArrayFromFile(__DIR__ . "/../../public/data/communes_ch.json");
        $cantonsArray = $this->getSwissCantons();
        // for each region -> create Region and persist
        foreach ($townsArray as $key => $item) {
            $town = new Town();
            if (array_key_exists("city", $item)) {
                $town->setName($item["city"]);
            }
            if (array_key_exists("admin", $item)) {
                foreach ($cantonsArray as $i => $canton) {
                    if (strpos($canton["cantonLongName"], $item["admin"])) {
                        $town->setScalarDepart($cantonsArray["cantonId"]);
                        $town->setCode($cantonsArray["cantonId"]);
                    }
                    //if (array_key_exists("cantonLongName", $canton))
                }
            }

            $manager->persist($town);
        }
    }

    private function getSwissCantons()
    {
        return $this->getDecodedArrayFromFile(__DIR__."/../../public/data/cantons_suisse.json");
    }

    public function loadBelgiumTowns(ObjectManager $manager)
    {

    }

    public static function getGroups(): array
    {
        return ['towns'];
    }
}