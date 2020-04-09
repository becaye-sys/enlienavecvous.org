<?php

namespace App\Form;

use App\Entity\Therapist;
use App\Entity\Town;
use App\Form\Localisation\LocalisationBeType;
use App\Form\Localisation\LocalisationChType;
use App\Form\Localisation\LocalisationFrType;
use App\Form\Localisation\LocalisationLuType;
use App\Repository\DepartmentRepository;
use App\Repository\TownRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TherapistRegisterType extends AbstractType
{
    private $departmentRepository;
    private $townRepository;

    public function __construct(DepartmentRepository $departmentRepository, TownRepository $townRepository)
    {
        $this->departmentRepository = $departmentRepository;
        $this->townRepository = $townRepository;
    }

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
            ->add(
                'firstName',
                TextType::class
            )
            ->add(
                'lastName',
                TextType::class
            )
            ->add(
                'country',
                ChoiceType::class,
                [
                    'choices' => [
                        "France" => 'fr',
                        "Belgique" => 'be',
                        "Luxembourg" => 'lu',
                        "Suisse" => 'ch'
                    ],
                    'preferred_choices' => ['fr']
                ]
            )
            ->add(
                'department',
                ChoiceType::class,
                [
                    'choices' => $this->departmentRepository->findBy(['country' => 'fr']),
                    'choice_label' => 'name',
                    'choice_value' => 'id'
                ]
            )
            ->add(
                'town',
                EntityType::class,
                [
                    'class' => Town::class,
                    'choices' => $this->getDefaultTowns(),
                    'choice_label' => 'name',
                    'choice_value' => 'id'
                ]
            )
            ->add(
                'phoneNumber',
                TelType::class
            )
            ->add(
                'ethicEntityCodeLabel',
                TextType::class
            )
            ->add(
                'schoolEntityLabel',
                TextType::class
            )
            ->add(
                'hasAcceptedTermsAndPolicies',
                CheckboxType::class
            )
            ->add(
                'hasCertification',
                CheckboxType::class
            )
            ->add(
                'isSupervised',
                CheckboxType::class
            )
            ->add(
                'isRespectingEthicalFrameWork',
                CheckboxType::class
            )
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Therapist $data */
            $data = $event->getData();
            $form = $event->getForm();
            if ($data->getCountry() !== null) {
                $country = $data->getCountry();
                $form->remove('department');
                $form->add(
                    'department',
                    ChoiceType::class,
                    [
                        'choices' => $this->getDepartmentByCountry($country)
                    ]
                );
            }
            if ($data->getDepartment() !== null) {
                $department = $data->getDepartment();
                $form->remove('town');
                $form->add(
                    'town',
                    EntityType::class,
                    [
                        'query_builder' => function (TownRepository $townRepository) use ($department) {
                        return $townRepository->findBy(
                            ['department' => $department],
                            ['code' => 'ASC']
                        );
                        }
                    ]
                );
            }
        });
    }

    private function getDepartmentByCountry(string $country): array
    {
        return $this->departmentRepository->findBy(
            ['country' => $country],
            ['code' => 'ASC']
        );
    }

    private function getDefaultTowns(): array
    {
        $depart = $this->getDepartmentByCountry("fr");
        $towns = $this->townRepository->findBy(['department' => $depart[0]]);
        return $towns;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Therapist::class,
        ]);
    }
}
