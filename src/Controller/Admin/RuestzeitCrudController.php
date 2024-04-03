<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\Ruestzeit;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class RuestzeitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Ruestzeit::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural("Rüstzeiten")
            ->setEntityLabelInSingular("Rüstzeit")
            ->setDateFormat('d.MM.Y')
            ->setTimeFormat('HH:mm')
            ->setDateTimeFormat('d.MM.Y HH:mm')
            ->renderContentMaximized()
            ->setTimezone('Europe/Berlin')
            ->setPageTitle('index', '%entity_label_plural% Übersicht')
            ->setPageTitle('edit', '%entity_label_singular% bearbeiten')
            // ->setHelp('edit', 'Helptext on Edit Page')
            ->setPageTitle('new', '%entity_label_singular% erstellen')

            ->setSearchFields(['title', 'description'])

            ->setDefaultSort(['registration_start' => 'DESC'])


            // ->setFormThemes(['my_theme.html.twig', 'admin.html.twig'])
            // ->addFormTheme('foo.html.twig')
            //  ->renderSidebarMinimized()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addColumn(6);        

        yield TextField::new('title', 'Titel');

        yield FormField::addColumn(6);        

        yield AssociationField::new('location', 'Ort')
            ->setCrudController(LocationCrudController::class);

        

        if($pageName != Crud::PAGE_INDEX) {
            yield FormField::addColumn(12);        
            yield TextEditorField::new('description', 'Beschreibung')->setTrixEditorConfig([

            ]);
        }

        if ($pageName == 'index') {
            yield IntegerField::new('memberCount', 'Teilnehmerzahl');
        }

        yield FormField::addColumn(6);
        yield IntegerField::new('memberlimit', 'Teilnehmerlimit');

        if($pageName != Crud::PAGE_INDEX) {
            yield FormField::addColumn(6);
            yield TextField::new('internalTitle', 'interne Bezeichnung')->setHelp('Angezeigt auf Unterschriftenliste');
        }

        yield FormField::addColumn(12);
        yield DateTimeField::new('registration_start', 'Anmeldestart')
            ->setTimezone("Europe/Berlin")
            ->setFormTypeOption('view_timezone', "Europe/Berlin")
        ;

        yield FormField::addColumn(6);
        yield UrlField::new('flyer_url', 'Flyer URL');
        
        yield FormField::addColumn(6);
        yield UrlField::new('image_url', 'Flyer Image URL');

        yield FormField::addColumn(6);
        yield DateField::new('date_from', 'Rüstzeit ab');
        
        yield FormField::addColumn(6);
        yield DateField::new('date_to', 'Rüstzeit bis');

        if($pageName != Crud::PAGE_INDEX) {
            yield FormField::addColumn(4);
            yield BooleanField::new('show_location', 'Rüstzeitort anzeigen');

            yield FormField::addColumn(4);
            yield BooleanField::new('show_dates', 'Rüstzeitdatum anzeigen');
        }
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryBuilder
            ->andWhere('entity.admin = :user')->setParameter(':user', $this->getUser()->getId());

        return $queryBuilder;
    }
}
