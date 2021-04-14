<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TelType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        if($options['registration']) {
            $builder
                ->add('email', EmailType::class, ['help'=>'Un email va vous être envoyé'])
                ->add('password', PasswordType::class, [
                    // instead of being set onto the object directly,
                    // this is read and encoded in the controller
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez entrer un mot de passe',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Votre mot de passe doit être de {{ limit }} characters au minimum',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                ])
            ;
        }
        else {
            $builder
                ->add('email', EmailType::class, ['help'=>'Un email va vous être envoyé si vous changez de mail'])
            ;
        }
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'J\'accepte la politique de confidentialité et les conditions d\'utilisation',
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devais accepter nos conditions d\'utilisation pour bénéficier de ce service.',
                    ]),
                ],
            ])
            ->add('postCode')
            ->add('phone', TelType::class, ['attr' => ['minlength' => 10, 'maxlength' => 10]])
            ->add('address')
            ->add('city')
            ->add('age')
            ->add('genre',ChoiceType::class,[
                'choices' => [
                    'Masculin' => 'Masculin',
                    'Féminin' => 'Féminin',],
                'expanded'=> true,
                'multiple'=> false,
                'choice_label' => false,
            ])
            ->add('isMedicalStaff',ChoiceType::class,[
                'choices' => [
                    'Oui' => true,
                    'Non' => false,],
                'expanded'=> true,
                'multiple'=> false,
                'choice_label' => false,
            ])
            ->add('hasComorbidity',ChoiceType::class,[
                'help'=>'Consulter la liste des comorbidités',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,],
                'expanded'=> true,
                'multiple'=> false,
                'choice_label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'registration' => false,
        ]);

        $resolver->setAllowedTypes('registration', 'bool');
    }
}
