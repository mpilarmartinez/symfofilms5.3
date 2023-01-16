<?php

namespace App\Form;

use App\Entity\Pelicula;
use App\Repository\PeliculaRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class ActorAddPeliculaFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        
            ->add('pelicula', EntityType::class, [
                'class' => Pelicula::class,
                'choice_label' => 'titulo',
                'label' => 'Añadir pelicula',
                
                // para definir el orden de los resultados
                'query_builder' => function (PeliculaRepository $er) {
                    return $er->createQueryBuilder('a')
                    ->orderBy('a.titulo', 'ASC');
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
