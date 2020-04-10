<?php

namespace App\Form;

use App\Entity\Department;
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
                    ]
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
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            dump($data);
        });
    }

    private function getDepartmentByCountry(?string $country = null): array
    {
        return $this->departmentRepository->findBy(
            ['country' => $country ?? 'fr'],
            ['code' => 'ASC']
        );
    }

    private function getTownsByDepartment(?Department $department = null): array
    {
        if ($department) {
            return $this->townRepository->findBy(['department' => $department]);
        } else {
            $department = $this->getDepartmentByCountry("fr");
            return $this->townRepository->findBy(['department' => $department[0]]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Therapist::class,
        ]);
    }
}
