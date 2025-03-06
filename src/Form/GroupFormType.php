<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;

class GroupFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Group Name',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter event name'],
                'constraints' => [new NotBlank(['message' => 'Group name cannot be blank'])],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Describe your event', 'rows' => 4],
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Event location'],
            ])
            ->add('event_date', DateTimeType::class, [
                'label' => 'Event Date',
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('max_participants', IntegerType::class, [
                'label' => 'Max Participants',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Max participants', 'min' => 1],
            ])
            ->add('visibility', ChoiceType::class, [
                'label' => 'Visibility',
                'choices' => ['Public' => 'public', 'Private' => 'private'],
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(['message' => 'Visibility must be selected'])],
            ])
            ->add('coverPicture', FileType::class, [
                'label' => 'Cover Picture',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
            ])
            ->add('groupType', EntityType::class, [
                'label' => 'Group Type *',
                'class' => GroupType::class,
                'choice_label' => 'type',
                'attr' => ['class' => 'form-control', 'required' => 'required'],
                'required' => true,
                'constraints' => [new NotBlank(['message' => 'Please select a group type'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('gt')
                        ->orderBy('gt.type', 'ASC');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
        ]);
    }
}