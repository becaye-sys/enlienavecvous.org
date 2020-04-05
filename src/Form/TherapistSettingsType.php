<?php


namespace App\Form;


use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TherapistSettingsType extends TherapistRegisterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->remove('password')
            ->remove('hasAcceptedTermsAndPolicies')
            ->remove('hasCertification')
            ->remove('isSupervised')
            ->remove('isRespectingEthicalFrameWork')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}