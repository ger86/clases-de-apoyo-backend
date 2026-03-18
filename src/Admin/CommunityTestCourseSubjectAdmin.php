<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\FormatterBundle\Form\Type\FormatterType;

class CommunityTestCourseSubjectAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('communityTest', ModelType::class, [
                'label' => 'Test de una Comunidad Autónoma',
                'btn_add' => false
            ])
            ->add('courseSubject', ModelType::class, [
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
                'ckeditor_context'     => 'default',
                'target_field'         => 'description',
                'label' => 'Descripción',
                'listener'     => true
            ]);
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('id', null, ['label' => 'id'])
            ->add('communityTest', null, ['label' => 'Test de una Comunidad Autónoma'])
            ->add('courseSubject', null, ['label' => 'Asignatura'])
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
            ->add('communityTest', null, ['label' => 'Comunidad Autónoma'])
            ->add('courseSubject', null, ['label' => 'Test'])
            ->add('description', null, ['label' => 'Descripción'])
            ->add('testYears', null, ['label' => 'Años']);
    }
}
