<?php

namespace App\Form;
use App\Entity\Competition;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CompetitionType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('Nom', TextType::class, [
            'label' => 'Nom de la compétition',
            'attr' => ['class' => 'form-control']
        ])
        ->add('Description', TextareaType::class, [
            'label' => 'Description',
            'attr' => ['class' => 'form-control']
        ])
        ->add('Date_debut', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de début',
            'attr' => ['class' => 'form-control']
        ])
        ->add('Date_fin', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de fin',
            'attr' => ['class' => 'form-control']
        ])
        ->add('Etat', ChoiceType::class, [
            'choices' => [
                'En attente' => 'en_attente',
                'En cours' => 'en_cours',
                'Terminée' => 'terminee',
            ],
            'label' => 'État',
            'attr' => ['class' => 'form-control']
        ])
        ->add('organisateur', EntityType::class, [
            'class' => User::class,
            'choice_label' => 'fullName',
            'label' => 'Organisateur',
            'placeholder' => 'Sélectionner un organisateur',
            'required' => false,
            'attr' => ['class' => 'form-control']
        ]);
    }
        public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Competition::class,
        ]);
    }
}