<?php

namespace App\Form;

use App\Entity\InfoCoach;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InfoCoachType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('price')
            ->add('description')            
            ->add('youtube')
            ->add('facebook')
            ->add('insta')
            ->add('twitch')          
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InfoCoach::class,
            'csrf_protection'    => false,
            'allow_extra_fields' => true,
        ]);
    }
}
