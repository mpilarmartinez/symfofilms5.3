<?php

namespace App\Form;

use App\Entity\Pelicula;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;


class PeliculaFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulo')
            ->add('director')
            ->add('duracion')
            ->add('genero')
            ->add('sinopsis')
            ->add('estreno')
            ->add('valoracion')
            ->add('caratula', FileType::class, [
                'label' => 'Caratula (jpg, png o gif):',
                'attr' => [
                    'class' => 'file-with-preview'
                ],
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
            'data_class' => Pelicula::class,
        ]);
    }
}
