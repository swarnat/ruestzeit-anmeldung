<?php

namespace App\Controller\Admin;

use App\AdminActions\Separator;
use App\ChoicesLoader\Landkreis;
use App\Entity\Anmeldung;
use App\Entity\Category;
use App\Entity\Ruestzeit;
use App\Enum\AnmeldungStatus;
use App\Enum\MealType;
use App\Enum\PersonenTyp;
use App\FieldTypes\CategorySelectionField;
use App\FieldTypes\CategorySelectionType;
use App\FieldTypes\TagType;
use App\Filter\LandkreisFilter;
use App\Generator\CurrentRuestzeitGenerator;
use App\Repository\RuestzeitRepository;
use App\Service\CsvExporter;
use App\Service\ExcelExporter;
use App\Service\SignaturelistExporter;
use App\Service\SignpaperExporter;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder as ORMQueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AnmeldungCrudController extends AbstractCrudController
{
    public function __construct(
        protected AdminUrlGenerator $adminUrlGenerator,
        protected RequestStack $requestStack,
        protected EntityManagerInterface $entityManager,
        protected CurrentRuestzeitGenerator $currentRuestzeitGenerator
    ) {
    }

    public function createEditForm(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormInterface
    {
        $result = parent::createEditForm($entityDto, $formOptions, $context);

        $currentRuestzeit = $this->currentRuestzeitGenerator->get();
        $relatedRuestzeit = $entityDto->getInstance()->getRuestzeit();

        if ($relatedRuestzeit->getId() !== $currentRuestzeit->getId()) {
            throw new Exception("Diese Anmeldung gehört zur Rüstzeit '" . $relatedRuestzeit->getTitle()."'.".PHP_EOL."Du bearbeitest gerade '".$currentRuestzeit->getTitle()."'");
        }
        
        return $result;
    }

    public static function getEntityFqcn(): string
    {
        return Anmeldung::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): ORMQueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryBuilder
            ->andWhere('entity.status = :status')->setParameter(':status', AnmeldungStatus::ACTIVE)
            ->andWhere('entity.ruestzeit = :ruestzeit_id')->setParameter(':ruestzeit_id', $this->currentRuestzeitGenerator->get()->getId())
        ;

        $queryBuilder->leftJoin('entity.categories', 'c')
            ->addSelect("c");

        return $queryBuilder;
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural("Teilnehmer")
            ->setEntityLabelInSingular("Teilnehmer")
            ->setDateFormat('d.MM.Y')
            ->setTimeFormat('HH:mm')
            ->setDateTimeFormat('d.MM.Y HH:mm')
            ->renderContentMaximized()
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

    public function configureActions(Actions $actions): Actions
    {
        $exportAction = Action::new('export')
            ->linkToUrl(function () {
                $request = $this->requestStack->getCurrentRequest();
                return $this->adminUrlGenerator->setAll($request->query->all())
                    ->setAction('export')
                    ->generateUrl();
            })
            // ... line 81
            ->setIcon('fa fa-download')

            ->createAsGlobalAction();
/*
        $signaturelistAction = Action::new('signaturelist', "Unterschriftenliste")
            ->linkToUrl(function () {
                $request = $this->requestStack->getCurrentRequest();
                return $this->adminUrlGenerator->setAll($request->query->all())
                    ->setAction('signaturelist')
                    ->generateUrl();
            })
            // ... line 81
            ->setIcon('fa fa-download')
            ->createAsGlobalAction();
*/
        $cancelAction = Action::new('cancel', "Stornieren")
            ->linkToCrudAction('cancel')
            ->setHtmlAttributes(['onclick' => 'return confirm("Bitte bestätigen Sie, dass Sie diesen Teilnehmer stornieren möchten")'])
            ->setIcon('fa fa-cancel')
            ->setCssClass('btn btn-danger')
            // ->setTemplatePath('CancelAction.html.twig')
        ;



        /** @var \EasyCorp\Bundle\EasyAdminBundle\Config\Actions $actions */
        return parent::configureActions($actions)
            // ->update(Crud::PAGE_INDEX, Action::DELETE, static function(Action $action) {
            //     $action->displayIf(static function (Anmeldung $question) {
            //         // always display, so we can try via the subscriber instead
            //         return true;
            //         //return !$question->getIsApproved();
            //     });
            // })
            // ->setPermission(Action::INDEX, 'ROLE_MODERATOR')
            // ->setPermission(Action::DETAIL, 'ROLE_MODERATOR')
            // ->setPermission(Action::EDIT, 'ROLE_MODERATOR')
            // ->setPermission(Action::NEW, 'ROLE_SUPER_ADMIN')
            // ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
            // ->setPermission(Action::BATCH_DELETE, 'ROLE_SUPER_ADMIN')
            // ->add(Crud::PAGE_DETAIL, $viewAction)
            // ->add(Crud::PAGE_INDEX, $viewAction)
            // ->add(Crud::PAGE_DETAIL, $approveAction)

            ->remove(Crud::PAGE_INDEX, "delete")

            ->add(Crud::PAGE_INDEX, $exportAction)

            // ->add(Crud::PAGE_INDEX, $signaturelistAction)
            ->add(Crud::PAGE_INDEX, $cancelAction)
            ->reorder(Crud::PAGE_INDEX, ["edit", "cancel"]);
    }


    public function createEntity(string $entityFqcn)
    {
        $entity = parent::createEntity($entityFqcn);

        $entity->setAgbAgree(true);
        $entity->setDsgvoAgree(true);
        return $entity;
    }

    public function configureFields(string $pageName): iterable
    {

        yield FormField::addColumn(12);

        if ($pageName == Crud::PAGE_NEW) {
            yield AssociationField::new('ruestzeit', 'Rüstzeit');
        }

        yield FormField::addColumn(6);

        // yield ChoiceField::new('status', 'Status der Anmeldung')
        //     ->setChoices(AnmeldungStatus::cases())
        //     ->setFormType(EnumType::class);

        yield TextField::new('lastname', 'Nachname')
            ->setCustomOption('xls-width', 200);

        yield TextField::new('firstname', 'Vorname')
            ->setCustomOption('xls-width', 200);

        yield ChoiceField::new('personenTyp', 'Typ')
            ->setChoices(PersonenTyp::cases())
            ->setFormType(EnumType::class);

        yield DateField::new('birthdate', 'Geburtstag');

        if ($pageName != Crud::PAGE_NEW) {
            yield IntegerField::new('age', 'Alter')
                ->setDisabled(true)
                ->setCustomOption('pdf-width', 30)
                ->setCustomOption('generated', true);
        }

        yield IntegerField::new('schoolclass', 'Schulklasse')
            ->setCustomOption('pdf-width', 20);

        if ($pageName == Crud::PAGE_INDEX) yield IntegerField::new('registrationPosition', 'Reg.Position');

        yield ChoiceField::new('mealtype', 'Verpflegung')
            ->setChoices(MealType::cases())
            ->setFormType(EnumType::class);


        if ($pageName == Crud::PAGE_NEW || $pageName == Crud::PAGE_EDIT) {
            yield ChoiceField::new('status', 'Status')
                ->setChoices(AnmeldungStatus::cases())
                ->setFormType(EnumType::class);

            yield IntegerField::new('registrationPosition', 'Reg.Position')->setCustomOption('generated', true);
        }

        yield FormField::addFieldset('Zahlung');

        $field = BooleanField::new('prepayment_done', 'Anzahlung');
        if ($pageName == Crud::PAGE_INDEX) {
            yield $field->setFormTypeOption('disabled', true);
        }
        yield $field;

        $field = BooleanField::new('payment_done', 'Zahlung ok');
        if ($pageName == Crud::PAGE_INDEX) {
            yield $field->setFormTypeOption('disabled', true);
        }
        yield $field;

        yield FormField::addFieldset();

        if ($pageName != 'index') yield TextareaField::new('notes', 'Anmerkungen');

        $createdAt = DateTimeField::new('createdAt')->setFormTypeOptions([
            'years' => range(date('Y'), date('Y') + 5),
            'widget' => 'single_text',
        ]);
        if (Crud::PAGE_EDIT === $pageName) {
            yield $createdAt->setFormTypeOption('disabled', true);
        }
        $createdAt->setTimezone("Europe/Berlin");
        $createdAt->setFormTypeOption('view_timezone', "Europe/Berlin");

        // yield AssociationField::new("categories", "Kategorien");
        yield CategorySelectionField::new("categories", "Kategorien");

        if (Crud::PAGE_INDEX != $pageName) {
            $agb = BooleanField::new('agb_agree', 'AGB akzeptiert');
            if (Crud::PAGE_NEW != $pageName) {
                $agb->setFormTypeOption('disabled', true);
            }
            yield $agb;

            $dsgvo = BooleanField::new('dsgvo_agree', 'Datenschutz Zustimmung');
            if (Crud::PAGE_NEW != $pageName) {
                $dsgvo->setFormTypeOption('disabled', true);
            }
            yield $dsgvo;
        }



        yield FormField::addColumn(6);

        yield FormField::addFieldset('Adresse');

        yield TextField::new('address', 'Strasse')->setRequired(false);

        if ($pageName != Crud::PAGE_INDEX) {
            yield TextField::new('postalcode', 'Postleitzahl')->setRequired(false);
        }

        yield TextField::new('city', 'Ort')->setRequired(false);
        yield TextField::new('landkreis', 'Landkreis')->setRequired(false);

        yield FormField::addFieldset('Kontakt');

        yield TextField::new('phone', 'Telefon');
        yield TextField::new('email', 'E-Mail');
    }

    public function configureFilters(Filters $filters): Filters
    {
        //$query = $em->createQuery('SELECT u FROM MyProject\Model\User u WHERE u.age > 20');
        //$users = $query->getResult();        

        return $filters
            ->add(EntityFilter::new('ruestzeit'))
            ->add(BooleanFilter::new('prepayment_done'))
            ->add(BooleanFilter::new('payment_done'))
            ->add(TextFilter::new('lastname'))
            ->add(TextFilter::new('postalcode'))
            ->add(TextFilter::new('city'))
            ->add(
                LandkreisFilter::new('landkreis')
                    ->setChoicesCallback(new Landkreis($this->entityManager, 1))
            )
            ->add(
                ChoiceFilter::new('mealtype')
                    ->setTranslatableChoices(
                        MealType::array()
                        // [
                        // MealType::ALL => \Symfony\Component\Translation\t((string)MealType::ALL, [], 'messages'),
                        // MealType::VEGETARIAN->value => \Symfony\Component\Translation\t((string)MealType::VEGETARIAN->value, [], 'messages'),
                        // MealType::VEGAN => \Symfony\Component\Translation\t((string)MealType::VEGAN, [], 'messages'),
                        // ],
                    )

            );
    }



    public function export(AdminContext $context, ExcelExporter $csvExporter)
    {
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_EDIT));
        $filters = $this->container->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());

        $queryBuilder = $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters);

        return $csvExporter->createResponseFromQueryBuilder($queryBuilder, $fields, 'Anmeldungen.xlsx');
    }

    public function signaturelist(AdminContext $context, SignaturelistExporter $csvExporter, RuestzeitRepository $ruestzeitRepository)
    {
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_EDIT));

        $ruestzeit = $ruestzeitRepository->findOneBy([]);

        return $csvExporter->generatePDF($ruestzeit, $fields, 'Unterschriften.pdf');
    }

    public function cancel(AdminContext $context, UrlGeneratorInterface $urlGenerator)
    {
        $anmeldung = $context->getEntity()->getInstance();

        $entityManager = $this->container->get('doctrine')->getManagerForClass(Anmeldung::class);

        $anmeldung->setStatus(AnmeldungStatus::CANCEL);

        $entityManager->persist($anmeldung);
        $entityManager->flush();


        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(AnmeldungCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl();
        return new RedirectResponse($url);
    }
}
