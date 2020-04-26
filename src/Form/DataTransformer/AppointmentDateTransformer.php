<?php


namespace App\Form\DataTransformer;


use Symfony\Component\Form\DataTransformerInterface;

class AppointmentDateTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('d/m/Y');
        }
    }

    public function reverseTransform($value)
    {
        return new \DateTime($value);
    }
}