<?php

namespace App\Form;

use App\Entity\Patient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Services\LocalisationFormTrait;

class PatientRegisterType extends AbstractType
{
    use LocalisationFormTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                EmailType::class
            )
            ->add(
                'password',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Les mots de passe ne correspondent pas.',
                    'options' => ['attr' => ['class' => 'password-field']],
                    'required' => true,
                    'first_options'  => ['label' => 'Mot de passe'],
                    'second_options' => ['label' => 'Confirmez votre mot de passe'],
                ]
            )
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add(
                'country',
                ChoiceType::class,
                [
                    'choices' => [
                        "France" => 'fr',
                        "Belgique" => 'be',
                        "Luxembourg" => 'lu',
                        "Suisse" => 'ch'
                    ]
                ]
            )
            ->add('phoneNumber', TextType::class)
            ->add('hasAcceptedTermsAndPolicies', CheckboxType::class)
            ->add('isMajor', CheckboxType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Patient $data */
            $data = $event->getData();
            $form = $event->getForm();
            if ($data->getCountry() !== null) {
                $country = $data->getCountry();
                $form->remove('department');
                $form->add(
                    'scalarDepartment',
                    ChoiceType::class,
                    [
                        'choices' => $this->getDepartmentByCountry($country)
                    ]
                );
            }
            if ($data->getScalarDepartment() !== null) {
                $department = $data->getScalarDepartment();
                dump('department:',$department);
                $form->remove('town');
                $form->add(
                    'scalarTown',
                    ChoiceType::class,
                    [
                        'choices' => $this->getTownsByDepartment()
                    ]
                );
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Patient::class,
        ]);
    }
}
