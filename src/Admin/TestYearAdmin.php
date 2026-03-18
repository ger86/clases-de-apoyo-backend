<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TestYearAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('year', TextType::class, ['label' => 'Año'])
            ->add('communityTestCourseSubject', ModelType::class, [
                'label' => 'Asignatura',
                'btn_add' => false
            ]);
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('id', null, ['label' => 'id'])
            ->add('year', null, ['label' => 'Año'])
            ->add('communityTestCourseSubject', null, ['label' => 'Asignatura'])
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
            ->add('year', null, ['label' => 'Año'])
            ->add('communityTestCourseSubject', null, ['label' => 'Asignatura']);
    }


    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('year', null, ['label' => 'Año'])
            ->add('communityTestCourseSubject', null, ['label' => 'Año'])
            ->add('exams', null, ['label' => 'Exámenes']);
    }
}
