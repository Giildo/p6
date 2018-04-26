<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire pour l'enregistrement d'un nouvel utilisateur.
 *
 * Class RegistryType
 * @package App\Form
 */
class RegistryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pseudo', TextType::class, ['label' => 'Pseudo *'])
            ->add('password', PasswordType::class, ['label' => 'Mot de passe *'])
            ->add('firstName', TextType::class, ['label' => 'Prénom *'])
            ->add('lastName', TextType::class, ['label' => 'Nom *'])
            ->add('mail', EmailType::class, ['label' => 'Email *'])
            ->add('phone', TelType::class, ['label' => 'Téléphone', 'required' => false])
            ->add('submit', SubmitType::class, ['label' => 'Valider'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
