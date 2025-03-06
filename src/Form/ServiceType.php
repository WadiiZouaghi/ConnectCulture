<?php

namespace App\Form;

use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Service name is required']),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Service name must be at least {{ limit }} characters long',
                        'maxMessage' => 'Service name cannot be longer than {{ limit }} characters'
                    ])
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter service name'],
                'label' => 'Service Name'
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Description is required'])
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter service description', 'rows' => 4],
                'label' => 'Description'
            ])
            ->add('serviceEquipments', CollectionType::class, [
                'entry_type' => ServiceEquipmentType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'attr' => ['class' => 'service-equipments'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}
