<?php

namespace App\Controller\Admin;

use App\Entity\CustomField;
use App\Enum\CustomFieldType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CustomFieldCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CustomField::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $entity = new CustomField();
        $entity->setOwner($this->getUser());

        return $entity;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Zusatzfeld')
            ->setEntityLabelInPlural('Zusatzfelder')
            ->setPageTitle('index', 'Zusatzfelder')
            ->setPageTitle('new', 'Neues Zusatzfeld')
            ->setPageTitle('edit', 'Zusatzfeld bearbeiten');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'Frage/Label');
        
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

        yield CollectionField::new('options', 'Optionen')
            ->setHelp('Eine Option pro Zeile (nur für Checkbox und Radio Box)')
            ->hideOnIndex()
            ->renderExpanded()
            ->setFormTypeOption('allow_add', true)
            ->setFormTypeOption('allow_delete', true)
            ->setFormTypeOption('by_reference', false);

        yield AssociationField::new('ruestzeit', 'Rüstzeit')
            ->autocomplete();

        if ($pageName === Crud::PAGE_INDEX) {
            yield AssociationField::new('owner', 'Ersteller');
        }
    }
}
