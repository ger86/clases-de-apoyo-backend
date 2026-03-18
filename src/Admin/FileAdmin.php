<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\AdminBundle\Form\Type\ModelListType;

class FileAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('name', TextType::class, ['label' => 'Nombre'])
            ->add(
                'file',
                ModelListType::class,
                [
                    'label' => 'Archivo',
                    'btn_add' => 'Crear nuevo',
                ],
                [
                    'link_parameters' => [
                        'context' => 'document',
                        'provider' => 'sonata.media.provider.file',
                    ]
                ]
            )
            ->add('weight', null, ['label' => 'Peso']);
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('id', null, ['label' => 'id'])
            ->add('name', null, ['label' => 'Nombre'])
            ->add('chapter', null, ['label' => 'Capítulo'])
            ->add('exam', null, ['label' => 'Examen'])
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
            ->add('file', null, ['label' => 'Archivo'])
            ->add('chapter', null, ['label' => 'Capítulo'])
            ->add('exam', null, ['label' => 'Exam'])
            ->add('weight', null, ['label' => 'Peso']);
    }
}
