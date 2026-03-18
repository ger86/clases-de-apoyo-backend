<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class DiscountCodeAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('show');
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add(
                'code',
                TextType::class,
                [
                    'label' => 'Código del cupón',
                    'required' => true
                ]
            )
            ->add('stripePlanId', null, ['label' => 'Stripe Plan Id'])
            ->add('validUntil', DateType::class, ['label' => 'Válido hasta'])
            ->add('price', null, ['label' => 'Precio tras aplicar el cupón']);
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('id')
            ->add('code', null, ['label' => 'Código'])
            ->add('validUntil', null, ['label' => 'Válido hasta'])
            ->add('price', null, ['label' => 'Precio'])
            ->add(ListMapper::NAME_ACTIONS, 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }
}
