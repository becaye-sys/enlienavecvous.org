<?php


namespace App\DataFixtures;


use App\Entity\Town;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TownFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        // load regions.json
        $townsFile = file_get_contents(__DIR__."/../../communes.json");
        // parse regions.json to array
        $townsArray = json_decode($townsFile, true);
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
            $manager->persist($town);
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['towns'];
    }
}