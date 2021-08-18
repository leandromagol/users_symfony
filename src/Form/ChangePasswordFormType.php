<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('new_password', TextType::class, [
                'required' => true,
                'constraints' => [new NotBlank(),new Length([
                    'min' => 8,
                    'max' => 50,
                    'minMessage' => 'Password name must be at least {{ limit }} characters long',
                    'maxMessage' => 'Password name cannot be longer than {{ limit }} characters',
                ])]
            ])
            ->add('repeat_new_password', TextType::class, [
                'required' => true,
                'constraints' => [new NotBlank(),new Length([
                    'min' => 8,
                    'max' => 50,
                    'minMessage' => 'Password name must be at least {{ limit }} characters long',
                    'maxMessage' => 'Password name cannot be longer than {{ limit }} characters',
                ])]
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ]);
    }
}
