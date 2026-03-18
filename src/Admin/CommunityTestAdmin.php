<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\FormatterBundle\Form\Type\FormatterType;

class CommunityTestAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('community', ModelType::class, [
                'label' => 'Comunidad Autónoma',
                'btn_add' => false
            ])
            ->add('knowledgeTest', ModelType::class, [
                'label' => 'Test',
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
            ->add('community', null, ['label' => 'Comunidad Autónoma'])
            ->add('knowledgeTest', null, ['label' => 'Test'])
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
            ->add('community', null, ['label' => 'Comunidad Autónoma'])
            ->add('knowledgeTest', null, ['label' => 'Test'])
            ->add('description', null, ['label' => 'Descripción'])
            ->add('communityTestCourseSubjects', null, ['label' => 'Asignaturas']);
    }
}
