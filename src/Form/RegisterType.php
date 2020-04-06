<?php


namespace App\Form;


use App\Entity\Department;
use App\Entity\Town;
use App\Entity\User;
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
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
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
                        "France" => "fr",
                        "Belgique" => "be",
                        "Luxembourg" => "lu",
                        "Suisse" => "ch"
                    ]
                ]
            )
            ->add(
                'department',
                EntityType::class,
                [
                    'class' => Department::class,
                    'choice_label' => 'name',
                    'choice_value' => 'code'
                ]
            )
            ->add('town', TextType::class)
            ->add(
                'hasAcceptedTermsAndPolicies',
                CheckboxType::class
            )
            ->add(
                'phoneNumber',
                TelType::class
            );
    }

    public function addTown($builder)
    {
        $builder->add(
            'town',
            EntityType::class,
            [
                'class' => Town::class,
                'choice_label' => 'name',
                'label' => "Votre ville",
                'query_builder' => function (TownRepository $townRepository) {
                    return $townRepository->createQueryBuilder('t')
                        ->where('t.department = :code')
                        ->setParameter('code', '01');
                },
                'required' => false
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}