<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\FormatterBundle\Form\Type\FormatterType;

class CourseSubjectAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('course', ModelType::class, [
                'label' => 'Curso',
                'btn_add' => false
            ])
            ->add('subject', ModelType::class, [
                'label' => 'Asignatura',
                'btn_add' => false
            ])
            ->add('format_type', FormatterType::class, [
                'required'             => false,
                'source_field'         => 'descriptionRaw',
                'format_field'         => 'descriptionFormatType',
                'format_field_options' => [
                    'choices' => ['richhtml'],
                    'data' => 'richhtml',
                ],
                'ckeditor_context' => 'default',
                'target_field'         => 'description',
                'label' => 'Descripción',
                'listener'     => true
            ])
            ->add('weight', null, ['label' => 'Peso']);
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('id', null, ['label' => 'id'])
            ->add('course', null, ['label' => 'Curso'])
            ->add('subject', null, ['label' => 'Asignatura'])
            ->add(ListMapper::NAME_ACTIONS, 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('course', null, ['label' => 'Curso'])
            ->add('subject', null, ['label' => 'Asignatura']);
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('name', null, ['label' => 'Nombre'])
            ->add('subject', null, ['label' => 'Asignatura'])
            ->add('course', null, ['label' => 'Curso'])
            ->add('chapterBlocks', null, ['label' => 'Bloques de capítulos'])
            ->add('description', null, ['label' => 'Descripción'])
            ->add('weight', null, ['label' => 'Peso']);
    }
}
