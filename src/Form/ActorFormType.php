<?php

namespace App\Form;

use App\Entity\Actor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ActorFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class)
            ->add('fechanacimiento', DateType::class, [
                'widget' => 'single_text'
            ])
            ->add('nacionalidad', TextType::class)
            ->add('biografia', TextareaType::class)
            ->add('fotografia', FileType::class, [
                'required'  =>  false,
                'data_class'   =>   NULL,
                'constraints'   =>  [
                    new File([
                        'maxSize'   =>  '10240k',
                        'mimeTypes' =>  ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage'  => 'Debes subir una imagen png, jpg o  gif'
                    ])
                    
                ]
                
            ])
                      
            ->add('Guardar', SubmitType::class, [
                'attr'  => ['class'=>'btn btn-success my-3']
            ]);          
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Actor::class,
        ]);
    }
}
