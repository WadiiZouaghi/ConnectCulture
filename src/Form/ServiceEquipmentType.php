<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\ServiceEquipment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class ServiceEquipmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Equipment name is required']),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Equipment name must be at least {{ limit }} characters long',
                        'maxMessage' => 'Equipment name cannot be longer than {{ limit }} characters'
                    ])
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter equipment name'],
                'label' => 'Equipment Name'
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Description is required'])
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter equipment description', 'rows' => 4],
                'label' => 'Description'
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a service',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'label' => 'Service'
            ])
            ->add('image', FileType::class, [
                'label' => 'Equipment Image',
                'mapped' => false, // Since it's not directly mapped to an entity field
                'required' => false, // Set this to false to make it optional
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, GIF)',
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ServiceEquipment::class,
        ]);
    }
}
