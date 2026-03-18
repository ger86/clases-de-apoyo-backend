<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelType;

class PremiumPaymentAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('user', ModelType::class, [
                'label' => 'Usuario',
                'btn_add' => false
            ])
            ->add('paymentId', null, ['label' => 'Id de pago']);
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('id', null, ['label' => 'Id'])
            ->add('user', null, ['label' => 'Usuario'])
            ->add('paymentId', null, ['label' => 'Id del pago'])
            ->add('createdAt', null, ['label' => 'Fecha de creación'])
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
            ->add('user', null, ['label' => 'Usuario'])
            ->add('paymentId', null, ['label' => 'Id del pago'])
            ->add('created', null, ['label' => 'Fecha de creación']);
    }
}
