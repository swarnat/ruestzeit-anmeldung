<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Ruestzeit;
use App\Generator\CurrentRuestzeitGenerator;
use App\Repository\RuestzeitRepository;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder as ORMQueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    private RuestzeitRepository $ruestzeitRepository;

    public function __construct(
        private CurrentRuestzeitGenerator $currentRuestzeitGenerator
        )
    {
        
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): ORMQueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        // Get the current Ruestzeit
        $currentRuestzeit = $this->getCurrentRuestzeit();
        
        if ($currentRuestzeit) {
            $queryBuilder
                ->andWhere('entity.ruestzeit = :ruestzeit')
                ->setParameter('ruestzeit', $currentRuestzeit);
        }
        
        return $queryBuilder;
    }

    public function configureActions(Actions $actions): Actions
    {
        $assignAction = Action::new('Zuweisungen')
            ->linkToRoute('teilnehmer_cat_assignments')
            // ... line 81
            ->setIcon('fa-solid fa-table-list')

            ->createAsBatchAction();

            return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, $assignAction);     
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural("Kategorien")
            ->setEntityLabelInSingular("Kategorie")
            ->setDateFormat('d.MM.Y')
            ->setTimeFormat('HH:mm')
            ->setDateTimeFormat('d.MM.Y HH:mm')
            ->renderContentMaximized()
            ->setTimezone('Europe/Berlin')
            ->setPageTitle('index', '%entity_label_plural%')
            ->setPageTitle('edit', '%entity_label_singular% bearbeiten')
            // ->setHelp('edit', 'Helptext on Edit Page')
            ->setPageTitle('new', '%entity_label_singular% erstellen')

            // ->setFormThemes(['my_theme.html.twig', 'admin.html.twig'])
            // ->addFormTheme('foo.html.twig')
            //  ->renderSidebarMinimized()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addColumn(6);

        yield TextField::new('title', 'Titel');
        yield ColorField::new('color', 'Farbe');

        yield FormField::addColumn(6);

        
    }

    public function createEntity(string $entityFqcn) {
        $entity = new Category();
        $entity->setUser($this->getUser());
        
        // Set the current Ruestzeit
        $currentRuestzeit = $this->getCurrentRuestzeit();
        if ($currentRuestzeit) {
            $entity->setRuestzeit($currentRuestzeit);
        }

        return $entity;
    }
    
    private function getCurrentRuestzeit(): ?Ruestzeit
    {
        // Get the current Ruestzeit from the session or a default one
        // This is a simplified example - you might want to implement a more sophisticated
        // way to determine the "current" Ruestzeit based on your application's logic
        
        // For now, we'll just get the first one as an example
        return $this->currentRuestzeitGenerator->get();
    }


}
