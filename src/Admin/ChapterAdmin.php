<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\Form\Type\CollectionType;
use Sonata\FormatterBundle\Form\Type\FormatterType;

class ChapterAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('name', TextType::class, ['label' => 'Nombre'])
            ->add('slug', TextType::class, ['label' => 'Slug', 'required' => false])
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
            ->add('chapterBlock', ModelType::class, [
                'label' => 'Bloque de capítulos',
                'btn_add' => false
            ])
            ->add('files', CollectionType::class, [
                'by_reference' => false,
                'type_options' => [
                    'delete' => true
                ],
                'label' => 'Archivos'
            ], [
                'edit' => 'inline',
                'inline' => 'table',
                'sortable' => 'position',
            ])
            ->add('weight', null, ['label' => 'Peso']);
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('name', null, ['label' => 'Nombre'])
            ->add('chapterBlock.courseSubject', null, ['label' => 'Asignatura'])
            ->add('chapterBlock', null, ['label' => 'Bloque de capítulos'])
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
            ->add('chapterBlock', null, ['label' => 'Bloque de capítulos'])
            ->add('chapterBlock.courseSubject', null, ['label' => 'Curso - Asignatura']);
    }


    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('name', null, ['label' => 'Nombre'])
            ->add('slug', null, ['label' => 'Slug'])
            ->add('description', null, ['label' => 'Descripción'])
            ->add('chapterBlock.courseSubject', null, ['label' => 'Asignatura'])
            ->add('chapterBlock', null, ['label' => 'Bloque de capítulos'])
            ->add('files', null, ['label' => 'Archivos'])
            ->add('weight', null, ['label' => 'Peso']);
    }
}
