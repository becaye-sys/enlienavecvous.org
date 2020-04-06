<?php

namespace App\Form;

use App\Entity\Therapist;
use App\Entity\Town;
use App\Repository\DepartmentRepository;
use App\Repository\TownRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TherapistRegisterType extends RegisterType
{
    private $departmentRepository;

    public function __construct(DepartmentRepository $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
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
                    EntityType::class,
                    [
                        'class' => Town::class,
                        'choice_label' => 'name',
                        'label' => "Votre ville",
                        'query_builder' => function (TownRepository $townRepository) use ($departCode) {
                        return $townRepository->createQueryBuilder('t')
                            ->where('t.department = :code')
                            ->setParameter('code', $departCode);
                        }
                    ]
                );
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
