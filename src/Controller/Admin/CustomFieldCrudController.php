<?php

namespace App\Controller\Admin;

use App\Entity\CustomField;
use App\Enum\CustomFieldType;
use App\Generator\CurrentRuestzeitGenerator;
use Doctrine\ORM\QueryBuilder as ORMQueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CustomFieldCrudController extends AbstractCrudController
{
    public function __construct(
        protected CurrentRuestzeitGenerator $currentRuestzeitGenerator
    )
    {
        
    }
    public static function getEntityFqcn(): string
    {
        return CustomField::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $entity = new CustomField();
        $entity->setOwner($this->getUser());
        $entity->setRuestzeit($this->currentRuestzeitGenerator->get());

        return $entity;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Zusatzfeld')
            ->setEntityLabelInPlural('Zusatzfelder')
            ->setPageTitle('index', 'Zusatzfelder aktuelle Rüstzeit')
            ->setPageTitle('new', 'Neues Zusatzfeld')
            ->setPageTitle('edit', 'Zusatzfeld bearbeiten');
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addColumn(12);
        yield TextField::new('title', 'Frage/Label');

        yield FormField::addColumn(6);
        yield BooleanField::new('optional', 'Optional')
            ->setHelp('Wenn aktiviert, muss dieses Feld nicht zwingend ausgefüllt werden');
        
        yield FormField::addColumn(6);            
        yield BooleanField::new('intern', 'Internes / Admin only Feld')
            ->setHelp('Wenn aktiviert wird dieses Feld nur in er Verwaltung dargestellt und kann vom Teilnehmer nicht selbst befüllt werden');
        
        yield FormField::addColumn(6);
        yield ChoiceField::new('type', 'Feldtyp')
            ->setChoices([
                'Textfeld' => CustomFieldType::INPUT,
                'Textbereich' => CustomFieldType::TEXTAREA,
                'Datum' => CustomFieldType::DATE,
                'Checkbox' => CustomFieldType::CHECKBOX,
                'Radio Box' => CustomFieldType::RADIO,
            ])
            ->renderExpanded()
            ->setHelp('Bei Checkbox und Radio Box können Sie unten die Optionen konfigurieren');

        yield FormField::addColumn(6);            

        yield CollectionField::new('options', 'Optionen')
            ->setHelp('Eine Option pro Zeile (nur für Checkbox und Radio Box)')
            ->hideOnIndex()
            ->renderExpanded()
            ->setFormTypeOption('allow_add', true)
            ->setFormTypeOption('allow_delete', true)
            ->setFormTypeOption('by_reference', false);

        yield FormField::addColumn(12);
        yield AssociationField::new('ruestzeit', 'Rüstzeit')
            ->setFormTypeOption('disabled', true)
            ->autocomplete();

        if ($pageName === Crud::PAGE_INDEX) {
            yield AssociationField::new('owner', 'Ersteller');
        }
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): ORMQueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryBuilder
            ->andWhere('entity.ruestzeit = :ruestzeit_id')->setParameter(':ruestzeit_id', $this->currentRuestzeitGenerator->get()->getId())
        ;

        return $queryBuilder;
    }    
}
