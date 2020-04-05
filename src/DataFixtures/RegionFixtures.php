<?php


namespace App\DataFixtures;


use App\Entity\Region;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class RegionFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        // load regions.json
        $regionFile = file_get_contents(__DIR__."/../../regions.json");
        //dump($regionFile);
        // parse regions.json to array
        $regionArray = json_decode($regionFile, true);
        // for each region -> create Region and persist
        foreach ($regionArray as $value) {
            $region = new Region($value["nom"], $value["code"]);
            $manager->persist($region);
        }
        // flush
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['regions'];
    }

}