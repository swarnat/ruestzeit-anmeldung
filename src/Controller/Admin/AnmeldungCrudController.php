<?php

namespace App\Controller\Admin;

use App\Entity\Anmeldung;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class AnmeldungCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Anmeldung::class;
    }


    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('ruestzeit');

        yield TextField::new('firstname', 'Vorname');
        yield TextField::new('lastname', 'Nachname');

        yield DateField::new('birthdate', 'Geburtstag');

        yield TextField::new('postalcode', 'Postleitzahl');
        yield TextField::new('city', 'Ort');
        yield TextField::new('address', 'Strasse');
        yield TextField::new('phone', 'Telefon');
        yield TextareaField::new('notes', 'Anmerkungen');

        yield BooleanField::new('prepayment_done', 'Anzahlung Überwiesen');
        yield BooleanField::new('payment_done', 'Zahlung komplett');

        $createdAt = DateTimeField::new('createdAt')->setFormTypeOptions([
            'years' => range(date('Y'), date('Y') + 5),
            'widget' => 'single_text',
        ]);
        
        if (Crud::PAGE_EDIT === $pageName) {
            yield $createdAt->setFormTypeOption('disabled', true);
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('ruestzeit'));
    }
}
