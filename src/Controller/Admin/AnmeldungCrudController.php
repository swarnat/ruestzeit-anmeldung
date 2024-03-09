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
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class AnmeldungCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Anmeldung::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural("Teilnehmer")
            ->setEntityLabelInSingular("Teilnehmer")
            ->setDateFormat('d.MM.Y')
            ->setTimeFormat('HH:mm')
            ->setDateTimeFormat('d.MM.Y HH:mm')
            // ->renderContentMaximized()
            ->setTimezone('Europe/Berlin')
            ->setPageTitle('index', '%entity_label_plural% Übersicht')
            ->setPageTitle('edit', '%entity_label_singular% bearbeiten')
            // ->setHelp('edit', 'Helptext on Edit Page')
            ->setPageTitle('new', '%entity_label_singular% erstellen')

            ->setSearchFields(['lastname', 'firstname', 'city', 'postalcode', 'address', 'notes'])

            ->setDefaultSort(['lastname' => 'ASC'])

            // ->setFormThemes(['my_theme.html.twig', 'admin.html.twig'])
            // ->addFormTheme('foo.html.twig')
            //  ->renderSidebarMinimized()
            ;
    }

    public function configureFields(string $pageName): iterable
    {

        yield FormField::addColumn(12); 
        
        if($pageName == Crud::PAGE_NEW) {
            yield AssociationField::new('ruestzeit', 'Rüstzeit');
        }
        
        yield FormField::addColumn(6);        

        yield TextField::new('firstname', 'Vorname');
        yield TextField::new('lastname', 'Nachname');

        yield DateField::new('birthdate', 'Geburtstag');
        
        if($pageName != Crud::PAGE_NEW) {
            yield IntegerField::new('age', 'Alter')->setDisabled(true);
        }
        

        yield FormField::addFieldset('Zahlung');

        yield BooleanField::new('prepayment_done', 'Anzahlung Überwiesen');
        yield BooleanField::new('payment_done', 'Zahlung komplett');

        yield FormField::addFieldset( );

        yield TextareaField::new('notes', 'Anmerkungen');

        $createdAt = DateTimeField::new('createdAt')->setFormTypeOptions([
            'years' => range(date('Y'), date('Y') + 5),
            'widget' => 'single_text',
        ]);
        if (Crud::PAGE_EDIT === $pageName) {
            yield $createdAt->setFormTypeOption('disabled', true);
        }

        yield FormField::addColumn(6);

        yield FormField::addFieldset('Adresse');

        yield TextField::new('address', 'Strasse');

        yield TextField::new('postalcode', 'Postleitzahl');
        yield TextField::new('city', 'Ort');
        
        yield FormField::addFieldset('Kontakt');

        yield TextField::new('phone', 'Telefon');
        
       
  
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('ruestzeit'))
            ->add(BooleanFilter::new('prepayment_done'))
            ->add(BooleanFilter::new('payment_done'))
            ->add(TextFilter::new('lastname'))
            ->add(TextFilter::new('postalcode'))
            ->add(TextFilter::new('city'))
            ;
    }
}
