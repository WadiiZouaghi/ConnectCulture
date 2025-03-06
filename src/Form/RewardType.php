<?php

namespace App\Form;

use App\Entity\Reward;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RewardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Text input for the name of the reward
            ->add('name', TextType::class, [
                'label' => 'Reward Name'
            ])
            
            // Integer input for the points required
            ->add('points_required', IntegerType::class, [
                'label' => 'Points Required'
            ])
            
            // A dropdown list for the user (assuming User entity has a `fullName` field)
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullName', // Assuming `fullName` method exists in `User` entity
                'label' => 'User',
                'placeholder' => 'Select a user' // Optional placeholder
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reward::class, // The form will be bound to the Reward entity
        ]);
    }
}
