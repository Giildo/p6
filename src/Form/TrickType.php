<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Trick;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('headPicture', PictureType::class, ['label' => 'Image à la Une'])
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('slug')
            ->add('description')
            ->add('published', CheckboxType::class, ['label' => 'Publier', 'required' => false])
            ->add('createdAt')
            ->add('updatedAt')
            ->add('category', EntityType::class, ['class' => Category::class, 'choice_label' => 'name'])
            ->add('user', EntityType::class, ['class' => User::class, 'choice_label' => 'pseudo'])
            ->add('pictures', CollectionType::class, [
                'label'         => 'Images',
                'entry_type'    => PictureType::class,
                'allow_add'     => true,
                'entry_options' => ['label' => false]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
                'attr'  => [
                    'class' => 'btn btn-success'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
