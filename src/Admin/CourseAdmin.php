<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\FormatterBundle\Form\Type\FormatterType;

class CourseAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('name', TextType::class, ['label' => 'Nombre'])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'help' => 'Dejar en blanco para que se genere automáticamente',
                'required' => false
            ])
            ->add('format_type', FormatterType::class, [
                'required'             => false,
                'source_field'         => 'descriptionRaw',
                'format_field'         => 'descriptionFormatType',
                'format_field_options' => [
                    'choices' => ['richhtml'],
                    'data' => 'richhtml',
                ],
                'ckeditor_context'     => 'default',
                'target_field'         => 'description',
                'label' => 'Descripción',
                'listener'     => true
            ])
            ->add('weight', null, ['label' => 'Peso']);
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('name', null, ['label' => 'Nombre'])
            ->add(ListMapper::NAME_ACTIONS, 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('name', null, ['label' => 'Nombre'])
            ->add('slug', null, ['label' => 'Slug'])
            ->add('description', null, ['label' => 'Descripción'])
            ->add('weight', null, ['label' => 'Peso'])
            ->add('courseSubjects', null, ['label' => 'Asignaturas']);
    }
}
