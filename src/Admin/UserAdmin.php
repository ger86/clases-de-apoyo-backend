<?php

namespace App\Admin;

use App\Entity\User;
use LogicException;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserAdmin extends AbstractAdmin
{

    private RoleHierarchyInterface $roleHierarchy;
    private UserPasswordHasherInterface $userPasswordHasher;

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::PER_PAGE] = 50;
    }

    public function setUserPasswordHasher(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function setRoles(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {

        $formMapper
            ->with('Datos Personales')
            ->add('email', TextType::class, ['label' => 'Email'])
            ->add('plainPassword', TextType::class, ['label' => 'Contraseña', 'required' => false])
            ->add('isVerified', null, ['label' => 'Usuario activado', 'required' => false])
            ->add('premiumUntil', DateType::class, ['label' => 'Premium hasta'])
            ->add('subscriptionId', TextType::class, ['label' => 'Id Suscripción', 'required' => false])
            ->add('subscriptionStatus', TextType::class, ['label' => 'Estado Suscripción', 'required' => false])
            ->add('customerId', TextType::class, ['label' => 'Id Customer', 'required' => false])
            ->end();
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $roles = [];
            foreach ($this->roleHierarchy->getReachableRoleNames(['ROLE_SUPER_ADMIN']) as $role => $v) {
                $roles[$v] = $role;
            }
            $formMapper->add(
                'roles',
                ChoiceType::class,
                [
                    'required' => true,
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => $roles
                ]
            );
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('email')
            ->add('isVerified');
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('email', null, ['label' => 'Email'])
            ->add('roles', null, ['label' => 'Roles'])
            ->add('isVerified', null, ['label' => 'Activado'])
            ->add('createdAt', null, ['label' => 'Creado'])
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
            ->add('id', null, ['label' => 'Id'])
            ->add('email', null, ['label' => 'Email'])
            ->add('roles', null, ['label' => 'Roles'])
            ->add('isVerified', null, ['label' => 'Activado'])
            ->add('createdAt', null, ['label' => 'Creado'])
            ->add('updatedAt', null, ['label' => 'Actualizado']);
    }

    public function preUpdate($user): void
    {
        if (!$user instanceof User) {
            throw new LogicException('This is impossible');
        }
        $hashedPassword = $this->userPasswordHasher->hashPassword(
            $user,
            $user->getPlainPassword()
        );
        $user->setPassword($hashedPassword);
    }
}
