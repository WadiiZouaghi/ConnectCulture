<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {   $builder->setAttribute('novalidate', 'novalidate');
        $builder
             ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Name is required']),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Name must be at least {{ limit }} characters long',
                        'maxMessage' => 'Name cannot be longer than {{ limit }} characters'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter event name'
                ],
                'label' => 'Event Name'
            ])
            ->add('destination', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Destination is required']),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Destination must be at least {{ limit }} characters long',
                        'maxMessage' => 'Destination cannot be longer than {{ limit }} characters'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter destination'
                ],
                'label' => 'Destination'
            ])
            ->add('eventtype', ChoiceType::class, [
                'choices' => [
                    'Select Event Type' => '',
                    'Sport' => 'sport',
                    'Cultural' => 'cultural',
                    'Educational' => 'educational',
                    'Entertainment' => 'entertainment',
                    'Other' => 'other'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please select an event type'])
                ],
                'attr' => ['class' => 'form-control'],
                'label' => 'Event Type'
            ])
            ->add('Description', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Enter event description'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please Enter description'])
                ],
                'required' => false,
                'label' => 'Description'
                
            ])
            ->add('equipment', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Enter required equipment'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please add equipment'])
                ],
                
                'required' => false,
                'label' => 'Equipment'
            ])
            ->add('image', FileType::class, [
                'label' => 'Event Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, GIF)',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
            ])
            ->add('nbplaces', IntegerType::class, [
                'label' => 'Number of Places',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}