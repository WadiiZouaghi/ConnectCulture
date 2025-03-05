<?php

namespace App\Form;

use App\Entity\Filtrage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FiltrageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mot_interdit', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'label' => 'Mot Interdit'
            ])
            ->add('action', ChoiceType::class, [
                'choices' => [
                    'remplacer' => 'remplacer',
                    'bloquer' => 'bloquer',
                ],
                'label' => 'Action',
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Select an action',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filtrage::class,
        ]);
    }
}
