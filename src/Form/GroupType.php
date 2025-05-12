<?php

namespace App\Form;

use App\Entity\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setAttribute('novalidate', 'novalidate');
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
                    'placeholder' => 'Enter group name'
                ],
                'label' => 'Group Name'
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Select Category' => '',
                    'Technology' => 'Technology',
                    'Art' => 'Art',
                    'Science' => 'Science',
                    'Sports' => 'Sports',
                    'Education' => 'Education',
                    'Entertainment' => 'Entertainment',
                    'Other' => 'Other'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please select a category'])
                ],
                'attr' => ['class' => 'form-control'],
                'label' => 'Category'
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Select Type' => '',
                    'Group' => 'Group',
                    'Club' => 'Club',
                    'Association' => 'Association',
                    'Team' => 'Team',
                    'Other' => 'Other'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please select a type'])
                ],
                'attr' => ['class' => 'form-control'],
                'label' => 'Type'
            ])
            ->add('city', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'City is required']),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'City cannot be longer than {{ limit }} characters'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter city'
                ],
                'label' => 'City'
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'label' => 'Start Date',
                'required' => false
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'label' => 'End Date',
                'required' => false
            ])
            ->add('size', IntegerType::class, [
                'constraints' => [
                    new PositiveOrZero(['message' => 'Size must be a positive number or zero'])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter maximum group size'
                ],
                'label' => 'Maximum Size',
                'required' => false
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Enter group description'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a description'])
                ],
                'required' => false,
                'label' => 'Description'
            ])
            ->add('visibility', ChoiceType::class, [
                'choices' => [
                    'Select Visibility' => '',
                    'Public' => 'PUBLIC',
                    'Private' => 'PRIVATE',
                    'Restricted' => 'RESTRICTED'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please select visibility'])
                ],
                'attr' => ['class' => 'form-control'],
                'label' => 'Visibility'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
        ]);
    }
}