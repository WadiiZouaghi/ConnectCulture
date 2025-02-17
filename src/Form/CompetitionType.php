<?php

namespace App\Form;

use App\Entity\Competition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints as Assert;

class CompetitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Nom', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 3]),
                ]
            ])
            ->add('Description', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add('Date_debut', DateType::class, [
                'widget' => 'single_text', // Allows date picker for the user
            ])
            ->add('Date_fin', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('Organisateur_ID', IntegerType::class, [ // Assuming it's an integer field
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Type('integer'),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Competition::class,
        ]);
    }
}