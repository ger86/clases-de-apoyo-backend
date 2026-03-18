<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\FormatterBundle\Form\Type\FormatterType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class YoutubeVideoAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('title', TextType::class, ['label' => 'Título'])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'help' => 'Dejar en blanco para que se genere automáticamente',
                'required' => false
            ])
            ->add('excerpt', TextareaType::class, ['label' => 'Resumen'])
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
            ->add('youtubeId', TextType::class, ['label' => 'Id de youtube'])
            ->add(
                'image',
                ModelListType::class,
                [
                    'required' => true,
                    'label' => 'Imagen'
                ],
                [
                    'link_parameters' => [
                        'context' => 'video_cover',
                        'provider' => 'sonata.media.provider.image',
                    ]
                ]
            );
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('id', null, ['label' => 'id'])
            ->add('title', null, ['label' => 'Título'])
            ->add('youtubeId', null, ['label' => 'Id de Youtube'])
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
            ->add('title', null, ['label' => 'Título'])
            ->add('youtubeId', null, ['label' => 'Id de youtube']);
    }
}
