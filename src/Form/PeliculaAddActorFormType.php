<?php

namespace App\Form;

use App\Entity\Actor;
use App\Repository\ActorRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class PeliculaAddActorFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        
            ->add('actor', EntityType::class, [
                'class' => Actor::class,
                'choice_label' => 'nombre',
                'label' => 'Añadir actor',
                
                // para definir el orden de los resultados
                'query_builder' => function (ActorRepository $er) {
                    return $er->createQueryBuilder('a')
                    ->orderBy('a.nombre', 'ASC');
                }
                
                ])
                
                
            ->add('Add', SubmitType::class, [
                'label' => 'Añadir',
                'attr' => ['class' =>'btn btn-success my-3']
            ])
            ->setAction($options['action']);
            
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NULL,
        ]);
    }

 }
