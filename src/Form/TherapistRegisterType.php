<?php

namespace App\Form;

use App\Entity\Therapist;
use App\Entity\Town;
use App\Repository\DepartmentRepository;
use App\Repository\TownRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TherapistRegisterType extends RegisterType
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
        parent::buildForm($builder, $options);
        $builder
            ->add(
                'ethicEntityCodeLabel',
                TextType::class,
                [
                    'attr' => [
                        'placeholder' => "Votre code de déontologie"
                    ]
                ]
            )
            ->add(
                'schoolEntityLabel',
                TextType::class,
                [
                    'attr' => [
                        'placeholder' => "Votre école de formation"
                    ]
                ]
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
            $form = $event->getForm();
            $data = $event->getData();
            if ($data->getDepartment() !== null) {
                $departCode = $data->getDepartment()->getCode();
                $form->remove('town');
                $form->add(
                    'town',
                    ChoiceType::class,
                    [
                        'label' => "Votre ville",
                        'choices' => $this->townRepository->findBy(['department' => $departCode]),
                        'choice_label' => 'name',
                        'choice_value' => 'code'
                    ]
                );

            }
        });
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            /** @var Therapist $data */
            $data = $event->getData();
            $town_code = $data["town"];
            $department_code = $data["department"];
            dump($data);
            if (null !== $town_code && null !== $department_code) {
                /** @var Town $town */
                $town = $this->townRepository->findOneBy(['code' => $town_code]);
                $data["town"] = $town;
                $department = $this->departmentRepository->findOneBy(['code' => $department_code]);
                $data["department"] = $department;
                dump($data);
            } else {
                dump($data->getTown());
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Therapist::class,
        ]);
    }
}
