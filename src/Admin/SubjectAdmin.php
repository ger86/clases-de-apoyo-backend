<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class SubjectAdmin extends AbstractAdmin
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
            ->add('description', TextareaType::class, ['label' => 'Descripción', 'required' => false]);
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('slug', null, ['label' => 'Slug'])
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
            ->add('name', null, ['label' => 'Nombre'])
            ->add('slug', null, ['label' => 'Slug'])
            ->add('description', null, ['label' => 'Descripción']);
    }
}
