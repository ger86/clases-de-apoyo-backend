<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Read-only window on what Apple told us about subscriptions. Useful when a user
 * says they paid but the account is not premium.
 */
class AppleNotificationAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::PER_PAGE] = 50;
        $sortValues[DatagridInterface::SORT_BY] = 'receivedAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper->add('note', null, ['label' => 'Resultado', 'required' => false]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('notificationType')
            ->add('originalTransactionId')
            ->add('environment');
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('receivedAt', null, ['label' => 'Recibida'])
            ->add('notificationType', null, ['label' => 'Tipo'])
            ->add('subtype', null, ['label' => 'Subtipo'])
            ->add('originalTransactionId', null, ['label' => 'Id compra'])
            ->add('environment', null, ['label' => 'Entorno'])
            ->add('note', null, ['label' => 'Resultado'])
            ->add(ListMapper::NAME_ACTIONS, 'actions', [
                'actions' => [
                    'show' => [],
                ]
            ]);
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id', null, ['label' => 'Id'])
            ->add('notificationUuid', null, ['label' => 'Uuid'])
            ->add('notificationType', null, ['label' => 'Tipo'])
            ->add('subtype', null, ['label' => 'Subtipo'])
            ->add('originalTransactionId', null, ['label' => 'Id compra'])
            ->add('environment', null, ['label' => 'Entorno'])
            ->add('note', null, ['label' => 'Resultado'])
            ->add('receivedAt', null, ['label' => 'Recibida']);
    }
}
