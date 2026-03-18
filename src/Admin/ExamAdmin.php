<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sonata\Form\Type\CollectionType;
use App\Enum\ExamType;
use Sonata\FormatterBundle\Form\Type\FormatterType;

class ExamAdmin extends AbstractAdmin
{

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('testYear.year', null, ['label' => 'Año'])
            ->add('testYear.communityTestCourseSubject.communityTest', null, ['label' => 'Comunidad'])
            ->add('testYear.communityTestCourseSubject.courseSubject', null, ['label' => 'Asignatura']);
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('testYear', ModelType::class, [
                'label' => 'Año',
                'btn_add' => 'Añadir nuevo'
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
            ->add('type', ChoiceType::class, [
                'choices' => ExamType::getTypes(),
                'label' => 'Tipo de examen'
            ])
            ->add('difficulty', TextType::class, ['label' => 'Dificultad'])
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
        $listMapper->addIdentifier('id', null, ['label' => 'id'])
            ->add('testYear', null, ['label' => 'Año'])
            ->add('name', null, ['label' => 'Nombre'])
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
            ->add('testYear', null, ['label' => 'Año'])
            ->add('name', null, ['label' => 'Nombre'])
            ->add('description', null, ['label' => 'Descripción'])
            ->add('type', null, ['label' => 'Tipo'])
            ->add('difficulty', null, ['label' => 'Dificultad'])
            ->add('files', null, ['label' => 'Archivos'])
            ->add('weight', null, ['label' => 'Peso']);
    }
}
