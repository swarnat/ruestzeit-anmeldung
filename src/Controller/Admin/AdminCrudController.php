<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct( UserPasswordHasherInterface $passwordEncoder ) {
        $this->passwordEncoder = $passwordEncoder;
    }

    public static function getEntityFqcn(): string
    {
        return Admin::class;
    }
    
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        // If user has ROLE_USER, only show their own data
        if (!$this->isGranted('ROLE_ADMIN') && $this->isGranted('ROLE_USER')) {
            /** @var Admin $currentUser */
            $currentUser = $this->getUser();
            $queryBuilder
                ->andWhere('entity.id = :current_user_id')
                ->setParameter(':current_user_id', $currentUser->getId());
        }
        
        return $queryBuilder;
    }

    /**
     * Restrict access to edit action for non-admin users
     */
    public function edit(AdminContext $context)
    {
        $admin = $context->getEntity()->getInstance();
        
        // If user is not an admin and trying to access another user's record
        /** @var Admin $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted('ROLE_ADMIN') && $currentUser->getId() !== $admin->getId()) {
            throw $this->createAccessDeniedException('You can only edit your own account.');
        }
        
        return parent::edit($context);
    }
    
    /**
     * Restrict access to detail action for non-admin users
     */
    public function detail(AdminContext $context)
    {
        $admin = $context->getEntity()->getInstance();
        
        // If user is not an admin and trying to access another user's record
        /** @var Admin $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted('ROLE_ADMIN') && $currentUser->getId() !== $admin->getId()) {
            throw $this->createAccessDeniedException('You can only view your own account.');
        }
        
        return parent::detail($context);
    }
    
    public function configureFields( string $pageName ): iterable {
        yield FormField::addColumn(6);

        yield FormField::addPanel( 'Benutzerdaten' )->setIcon( 'fa fa-user' )->setColumns(6);
        
        yield TextField::new( 'username' )->onlyOnIndex();
        
        yield ArrayField::new( 'roles' )->onlyOnIndex();
        
        yield TextField::new( 'username' )->onlyWhenCreating();
        yield TextField::new( 'username' )->onlyWhenUpdating()->setDisabled();
        
        yield TextField::new( 'email' );
        
        // Add role editing field - only visible to ROLE_ADMIN users when updating
        // This allows admins to change a user's role from ROLE_ADMIN to ROLE_USER
        if ($this->isGranted('ROLE_ADMIN')) {
            yield ArrayField::new('roles')
                ->onlyWhenUpdating()
                ->setHelp('Available roles: ROLE_USER, ROLE_ADMIN. The ROLE_USER role is always added automatically. To downgrade an admin, remove ROLE_ADMIN from the array.');
        }

        yield FormField::addColumn(6);
                
        yield FormField::addPanel( 'Change password' )->setIcon( 'fa fa-key' )->setColumns(6);

        yield Field::new( 'password', 'New password' )->onlyWhenCreating()->setRequired( true )
                   ->setFormType( RepeatedType::class )
                   ->setFormTypeOptions( [
                       'type'            => PasswordType::class,
                       'first_options'   => [ 'label' => 'New password' ],
                       'second_options'  => [ 'label' => 'Repeat password' ],
                       'error_bubbling'  => true,
                       'invalid_message' => 'The password fields do not match.',
                   ] );
                   
        yield Field::new( 'password', 'New password' )->onlyWhenUpdating()->setRequired( false )
                   ->setFormType( RepeatedType::class )
                   ->setFormTypeOptions( [
                       'type'            => PasswordType::class,
                       'first_options'   => [ 'label' => 'New password' ],
                       'second_options'  => [ 'label' => 'Repeat password' ],
                       'error_bubbling'  => true,
                       'invalid_message' => 'The password fields do not match.',
                   ] );
    }
    
    public function createEditFormBuilder( EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context ): FormBuilderInterface {
        $plainPassword = $entityDto->getInstance()?->getPassword();
        $formBuilder   = parent::createEditFormBuilder( $entityDto, $formOptions, $context );
        $this->addEncodePasswordEventListener( $formBuilder, $plainPassword );
        
        // Add protection for role editing
        $this->addRoleProtectionEventListener($formBuilder, $entityDto->getInstance());

        return $formBuilder;
    }

    public function createNewFormBuilder( EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context ): FormBuilderInterface {
        $formBuilder = parent::createNewFormBuilder( $entityDto, $formOptions, $context );
        $this->addEncodePasswordEventListener( $formBuilder );

        return $formBuilder;
    }

    protected function addEncodePasswordEventListener( FormBuilderInterface $formBuilder, $plainPassword = null ): void {
        $formBuilder->addEventListener( FormEvents::SUBMIT, function ( FormEvent $event ) use ( $plainPassword ) {
            /** @var Admin $user */
            $user = $event->getData();
            if ( $user->getPassword() !== $plainPassword ) {
                $user->setPassword( $this->passwordEncoder->hashPassword( $user, $user->getPassword() ) );
            }
        } );
    }
    
    /**
     * Adds protection to prevent unauthorized role changes
     */
    protected function addRoleProtectionEventListener(FormBuilderInterface $formBuilder, Admin $originalAdmin = null): void {
        // Store the original roles
        $originalRoles = $originalAdmin ? $originalAdmin->getRoles() : [];
        
        $formBuilder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($originalRoles) {
            $data = $event->getData();
            
            // If the form contains roles data and user is not an admin
            if (isset($data['roles']) && !$this->isGranted('ROLE_ADMIN')) {
                // Remove the roles field from the submitted data to prevent changes
                unset($data['roles']);
                $event->setData($data);
            }
        });
        
        $formBuilder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($originalRoles, $originalAdmin) {
            /** @var Admin $user */
            $user = $event->getData();
            
            // If user is not an admin, restore original roles
            if (!$this->isGranted('ROLE_ADMIN') && $originalAdmin) {
                // Filter out ROLE_USER which is added automatically
                $originalRolesFiltered = array_filter($originalRoles, function($role) {
                    return $role !== 'ROLE_USER';
                });
                
                $user->setRoles($originalRolesFiltered);
            }
        });
    }
    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
