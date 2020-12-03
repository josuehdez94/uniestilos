<?php

namespace App\Form\Backend\Articulo;

use App\Entity\Articulo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ArticuloType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sku', null,[
                'attr' => ['class' => 'form-control form-control-sm']
            ])
            ->add('categoria', null,[
                'placeholder' => '==Selecciona una marca',
                'attr' => ['class' => 'form-control form-control-sm']
            ])
            ->add('descripcion',null,[
                'help' => 'La descipcion debe contener al menos 10 caracteres y maximo 255.',
                'attr' => ['class' => 'form-control form-control-sm']
            ])
            ->add('precio1', null,[
                'attr' => ['class' => 'form-control form-control-sm']
            ])
            ->add('precio2',null,[
                'attr' => ['class' => 'form-control form-control-sm']
            ])
            ->add('activo', CheckboxType::class,[
                'required' => false,
                'help' => 'Al marcar este campo el articulo aparecera en la pagina principal.',
                'label' => '¿Activo?',
                'attr' => ['class' => 'form-control form-control-sm']
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Articulo::class,
            'validation_groups' => ['backend_articulo_nuevo', 'backend_articulo_editar']
        ]);
    }
}
