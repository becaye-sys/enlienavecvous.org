<?php


namespace App\Services;


use Cocur\Slugify\Slugify;

trait FixturesTrait
{
    public function getDecodedArrayFromFile(string $file): array
    {
        $file = file_get_contents($file);
        // parse regions_fr.json to array
        $array = json_decode($file, true);
        return $array;
    }

    public function getSlug(string $string): string
    {
        $slug = new Slugify();
        return $slug->slugify($string);
    }
}