<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\FormatterBundle\Form\Type\FormatterType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ArticleAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('show');
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('Contenido')
            ->add('title', TextType::class, ['label' => 'Título'])
            ->add('slug', TextType::class, ['label' => 'Slug', 'required' => false])
            ->add('excerpt', TextType::class, ['required' => true, 'label' => 'Resumen'])
            ->add('format_type', FormatterType::class, [
                'required'             => false,
                'source_field'         => 'text',
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
            ->add('skipCover', null, ['label' => 'No mostrar portada en página del artículo'])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper->add('slug')->add('title');
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('slug', null, ['label' => 'slug'])
            ->add('title', null, ['label' => 'título'])
            ->add('createdAt', null, ['label' => 'fecha creación'])
            ->add('updatedAt', null, ['label' => 'fecha actualización'])
            ->add(ListMapper::NAME_ACTIONS, 'actions', [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }
}
