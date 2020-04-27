<?php


namespace App\TwigExtension;


use App\Entity\User;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PrefixPhoneNumberExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('phone_prefix', [$this, 'showPrefixPhoneNumber']),
        ];
    }

    public function showPrefixPhoneNumber(string $country, string $phoneNumber)
    {
        return User::PHONE_PREFIX[$country] . $phoneNumber;
    }
}