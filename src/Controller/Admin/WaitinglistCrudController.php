<?php

namespace App\Controller\Admin;

use App\Entity\Anmeldung;
use App\Enum\AnmeldungStatus;
use App\Enum\MealType;
use App\Generator\CurrentRuestzeitGenerator;
use App\Service\CsvExporter;
use App\Service\ExcelExporter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection as CollectionFilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WaitinglistCrudController extends AnmeldungCrudController
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, CollectionFilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryBuilder
            ->orderBy('entity.registrationPosition', 'ASC')
            ->andWhere('entity.ruestzeit = :ruestzeit_id')->setParameter(':ruestzeit_id', $this->currentRuestzeitGenerator->get()->getId())
            ->andWhere('entity.status = :status')->setParameter(':status', AnmeldungStatus::WAITLIST);

        return $queryBuilder;
    }

    public function configureFields(string $pageName, ?AdminContext $context = null): iterable
    {
        if ($pageName != "index") {
            yield from parent::configureFields($pageName);
            return;
        }

        // yield ChoiceField::new('status', 'Status der Anmeldung')
        //     ->setChoices(AnmeldungStatus::cases())
        //     ->setFormType(EnumType::class);

        yield TextField::new('firstname', 'Vorname');
        yield TextField::new('lastname', 'Nachname');

        yield IntegerField::new('registrationPosition', 'Registrierungsposition');

        yield DateField::new('birthdate', 'Geburtstag');

        if($this->currentRuestzeitGenerator->get()->isShowRoomRequest()) {
            yield ChoiceField::new('roomRequest', 'Raumwunsch');
        }

        if($this->currentRuestzeitGenerator->get()->isShowRoommate()) {
            yield TextField::new('roommate', 'Zimmerpartner');
        }
        
        yield ChoiceField::new('mealtype', 'Verpflegung')
            ->setChoices(MealType::cases())
            ->setFormType(EnumType::class);

        if ($pageName != Crud::PAGE_NEW) {
            yield IntegerField::new('age', 'Alter')->setDisabled(true);
        }


        yield FormField::addFieldset('Zahlung');

        yield BooleanField::new('prepayment_done', 'Anzahlung Überwiesen');
        yield BooleanField::new('payment_done', 'Zahlung komplett');

        yield FormField::addFieldset();

        $createdAt = DateTimeField::new('createdAt')->setFormTypeOptions([
            'years' => range(date('Y'), date('Y') + 5),
            'widget' => 'single_text',
        ]);
        if (Crud::PAGE_EDIT === $pageName) {
            yield $createdAt->setFormTypeOption('disabled', true);
        }

        yield FormField::addColumn(6);

        if ($pageName != Crud::PAGE_INDEX) {
            yield TextField::new('address', 'Strasse');

            yield TextField::new('postalcode', 'Postleitzahl');
        }
        yield TextField::new('landkreis', 'Landkreis');

        yield TextField::new('city', 'Ort');

        yield FormField::addFieldset('Kontakt');

        yield TextField::new('phone', 'Telefon');


        // 
    }

    public function configureActions(Actions $actions): Actions
    {;
        $actions = parent::configureActions($actions);

        $activateAction = Action::new('activate')
            ->linkToCrudAction('anmeldungen_activate')
            // ... line 81
            ->setIcon('fa fa-check')
            // ->createAsGlobalAction()
            ;

        $actions
            ->add(Crud::PAGE_INDEX, $activateAction);

        return $actions;
    }

    #[AdminAction(routePath: '/{entityId}/activate', routeName: 'anmeldungen_activate', methods: ['GET'])]
    public function anmeldungen_activate(AdminContext $context, UrlGeneratorInterface $urlGenerator) {
        $anmeldung = $context->getEntity()->getInstance();
        
        $entityManager = $this->container->get('doctrine')->getManagerForClass(Anmeldung::class);

        $anmeldung->setStatus(AnmeldungStatus::ACTIVE);

        $entityManager->persist($anmeldung);
        $entityManager->flush();


        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(AnmeldungCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl();
        return new RedirectResponse($url);
    }

}
