<?php

// src/Form/AddToCartType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AddToCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $b, array $options): void
    {
        $max = $options['max'] ?? null; // stock dispo ou limite UI



        $b->add('quantity', IntegerType::class, [
            'label' => 'Quantité',
            'data'  => 1,
            'attr'  => [
                'min' => 0,
                'max' => $max ?: 99,
                'step' => 1,
                'inputmode' => 'numeric',
                'pattern'   => '\d*',
            ],

            'constraints' => array_filter([
                new Assert\NotBlank(message: 'Veuillez choisir une quantité.'),
                new Assert\Positive(message: 'La quantité doit être positive.'),
                $max ? new Assert\LessThanOrEqual(
                    value: $max,
                    message: 'Stock insuffisant (max {{ compared_value }}).'
                ) : null,
            ]),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['max' => null]); // passe le stock via cette option
    }
}
