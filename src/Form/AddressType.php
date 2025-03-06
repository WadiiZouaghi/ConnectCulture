<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('longitude', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Longitude is required'])
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter longitude'],
                'label' => 'Longitude'
            ])
            ->add('latitude', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Latitude is required'])
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter latitude'],
                'label' => 'Latitude'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
