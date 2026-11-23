<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Read-only list of the emails left for the free exam pack. The consent date and
 * IP are the proof of consent if anybody asks.
 */
class LeadSubscriberAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'show', 'export']);
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::PER_PAGE] = 50;
        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('email')
            ->add('sourcePackCode', null, ['label' => 'Pack de origen'])
            ->add('followUpStep', null, ['label' => 'Seguimientos enviados']);
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('email', null, ['label' => 'Email'])
            ->add('createdAt', null, ['label' => 'Alta'])
            ->add('confirmedAt', null, ['label' => 'Confirmado'])
            ->add('unsubscribedAt', null, ['label' => 'Baja'])
            ->add('sourcePackCode', null, ['label' => 'Pack de origen'])
            ->add('followUpStep', null, ['label' => 'Seguimientos enviados'])
            ->add(ListMapper::NAME_ACTIONS, 'actions', [
                'actions' => [
                    'show' => [],
                ]
            ]);
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('email', null, ['label' => 'Email'])
            ->add('createdAt', null, ['label' => 'Alta'])
            ->add('consentAt', null, ['label' => 'Consentimiento'])
            ->add('consentIp', null, ['label' => 'IP del consentimiento'])
            ->add('confirmationSentAt', null, ['label' => 'Último email de confirmación'])
            ->add('confirmedAt', null, ['label' => 'Confirmado'])
            ->add('unsubscribedAt', null, ['label' => 'Baja'])
            ->add('sourcePackCode', null, ['label' => 'Pack de origen'])
            ->add('followUpStep', null, ['label' => 'Seguimientos enviados'])
            ->add('lastFollowUpAt', null, ['label' => 'Último seguimiento'])
            ->add('nextFollowUpAt', null, ['label' => 'Próximo seguimiento']);
    }

    protected function configureExportFields(): array
    {
        return ['email', 'createdAt', 'consentAt', 'confirmedAt', 'unsubscribedAt', 'sourcePackCode', 'followUpStep'];
    }
}
