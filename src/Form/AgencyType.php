<?php

namespace App\Form;

use App\Entity\Agency;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class AgencyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('location')
            ->add('longitude')
            ->add('latitude')
            ->add('email')
            ->add('userId', IntegerType::class, [
        'required' => true,
        'label' => 'User ID',
    ])
    ->add('services', EntityType::class, [
        'class' => Service::class,
        'choice_label' => 'name',
        'multiple' => true,
        'expanded' => false, // dropdown multi-select
        'label' => 'Services',
    ]);
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Agency::class,
        ]);
    }
}
