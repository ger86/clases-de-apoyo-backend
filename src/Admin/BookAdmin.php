<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\FormatterBundle\Form\Type\FormatterType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class BookAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('Contenido')
            ->add('title', TextType::class, ['label' => 'Título'])
            ->add('slug', TextType::class, ['required' => false])
            ->add('price', NumberType::class, ['required' => true, 'label' => 'Precio'])
            ->add('format_type', FormatterType::class, [
                'required'             => false,
                'source_field'         => 'textRaw',
                'format_field'         => 'formatType',
                'format_field_options' => [
                    'choices' => ['richhtml'],
                    'data' => 'richhtml',
                ],
                'ckeditor_context'     => 'default',
                'target_field'         => 'text',
                'label' => 'Cuerpo del artículo',
                'listener'     => true
            ])
            ->add(
                'image',
                ModelListType::class,
                [
                    'required' => true,
                    'label' => 'Imagen'
                ],
                [
                    'link_parameters' => [
                        'context' => 'article',
                        'provider' => 'sonata.media.provider.image',
                    ]
                ]
            )
            ->add('file', ModelListType::class, [
                'label' => 'Archivo',
                'btn_add' => 'Crear nuevo'
            ])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper->add('slug')->add('title');
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('title')
            ->add(ListMapper::NAME_ACTIONS, 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }
}
