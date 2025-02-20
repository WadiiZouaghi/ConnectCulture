<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints as Assert;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'événement',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de l\'événement est obligatoire.']),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'Le nom de l\'événement doit contenir au moins {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description est obligatoire.']),
                ],
            ])
            ->add('date', DateType::class, [
                'label' => 'Date de l\'événement',
                'widget' => 'single_text', // Utilise un champ datetime-local
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de l\'événement est obligatoire.']),
                ],
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu de l\'événement',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le lieu de l\'événement est obligatoire.']),
                ],
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
