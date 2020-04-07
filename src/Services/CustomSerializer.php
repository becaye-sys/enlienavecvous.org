<?php


namespace App\Services;


use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CustomSerializer
{
    public function serialize($dataToSerialize, array $ignoredAttributes)
    {
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer([$normalizer], [$encoder]);
        return $serializer->serialize($dataToSerialize, 'json', ['ignored_attributes' => $ignoredAttributes]);
    }
}