<?php


namespace App\DataFixtures;


use App\Entity\Department;
use App\Entity\Town;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TownFixtures extends Fixture implements FixtureGroupInterface
{
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
        $townsArray = $this->getDecodedArrayFromFile(__DIR__ . "/../../communes_fr.json");
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
                $town->setDepartment($item["codeDepartement"]);
            }
            if (array_key_exists("codeRegion", $item)) {
                $town->setRegion($item["codeRegion"]);
            }
            if (array_key_exists("codesPostaux", $item)) {
                $town->setZipCodes($item["codesPostaux"]);
            }
            if (array_key_exists("population", $item)) {
                $town->setPopulation($item["population"]);
            }
            $this->addReference(self::TOWN_FR_REFERENCE . "_" . $item["code"], $town);
            $manager->persist($town);
        }
    }

    private function loadLuxembourgTowns(ObjectManager $manager)
    {
        $townsArray = $this->getDecodedArrayFromFile(__DIR__ . "/../../communes_lu.json");
        // for each region -> create Region and persist
        foreach ($townsArray as $key => $item) {
            $town = new Town();
            if (array_key_exists("COMMUNE", $item)) {
                $town->setName($item["COMMUNE"]);
            }
            if (array_key_exists("LAU2", $item)) {
                $town->setDepartment(substr($item["LAU2"], 0, 1));
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
        $townsArray = $this->getDecodedArrayFromFile(__DIR__ . "/../../communes_ch.json");
        $departArray = $this->getDecodedArrayFromFile(__DIR__ . "/../../departements_ch.json");
        // for each region -> create Region and persist
        foreach ($townsArray as $key => $item) {
            $town = new Town();
            if (array_key_exists("city", $item)) {
                $town->setName($item["city"]);
            }
            if (array_key_exists("admin", $item)) {
                $town->setDepartment("depart");
            }
            if (array_key_exists("LAU2", $item)) {
                $town->setZipCodes([$item["LAU2"]]);
            }

            $manager->persist($town);
        }
    }

    public function loadBelgiumTowns(ObjectManager $manager)
    {
        //$townsArray = $this->getDecodedArrayFromFile(__DIR__."/../../");
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
        return ['towns'];
    }
}