<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ChapterBlockAdmin extends AbstractAdmin
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
            ->add('description', TextareaType::class, ['label' => 'Descripción'])
            ->add('courseSubject', ModelType::class, [
                'label' => 'Asignatura',
                'btn_add' => false
            ])
            ->add('weight', null, ['label' => 'Peso']);
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('name', null, ['label' => 'Nombre'])
            ->add('courseSubject', null, ['label' => 'Asignatura'])
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
            ->add('courseSubject', null, ['label' => 'Curso - Asignatura']);
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('name', null, ['label' => 'Nombre'])
            ->add('slug', null, ['label' => 'Slug'])
            ->add('description', null, ['label' => 'Descripción'])
            ->add('weight', null, ['label' => 'Peso'])
            ->add('courseSubject', null, ['label' => 'Asignaturas'])
            ->add('chapters', null, ['label' => 'Capítulos']);
    }
}
