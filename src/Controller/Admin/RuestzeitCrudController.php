<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\Ruestzeit;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Ehyiah\QuillJsBundle\DTO\Fields\BlockField\HeaderField;
use Ehyiah\QuillJsBundle\DTO\QuillGroup;
use Ehyiah\QuillJsBundle\Form\QuillAdminField;
use Ehyiah\QuillJsBundle\Form\QuillType;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class RuestzeitCrudController extends AbstractCrudController
{
    public function __construct(
        protected AdminUrlGenerator $adminUrlGenerator,
        protected RequestStack $requestStack,
        protected EntityManagerInterface $entityManager
    ) {
    }
        
    public static function getEntityFqcn(): string
    {
        return Ruestzeit::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural("Rüstzeiten")
            ->setEntityLabelInSingular("Rüstzeit")
            ->setDateFormat('dd.MM.Y')
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

            ->overrideTemplate("crud/edit", "admin/ruestzeit_edit.html.twig")
            
            // ->setFormThemes(['my_theme.html.twig', 'admin.html.twig'])
            // ->addFormTheme('foo.html.twig')
            //  ->renderSidebarMinimized()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $passwordLink = Action::new('link_password', "Link inkl. Passwort")
            ->linkToUrl(function (Ruestzeit $ruestzeit) {
                return '/?pw=' . $ruestzeit->getPassword();
            })
            ->setHtmlAttributes(["target" => "_blank"])
            ->displayIf(function (Ruestzeit $ruestzeit) {
                return $ruestzeit->getPassword() != '';
            })
            ->setIcon('fa fa-link');

        
            $shortLink = Action::new('link_registration_short', "Kurzlink")
            ->linkToUrl(function (Ruestzeit $ruestzeit) {
                return '/' . $ruestzeit->getForwarder();
            })
            ->setHtmlAttributes(["target" => "_blank"])
            ->setIcon('fa fa-link');


            $longLink = Action::new('link_registration_long', "Komplette URL")
            ->linkToUrl(function (Ruestzeit $ruestzeit) {
                return '/r/' . $ruestzeit->getSlug();
            })
            ->setHtmlAttributes(["target" => "_blank"])
            ->setIcon('fa fa-link');


            $longLink = Action::new('ruestzeit_activate', "Ruestzeit wechseln")
                ->linkToCrudAction('activate_ruestzeit')
                ->setIcon('fa fa-download');

            $qrCode = Action::new('show_qrcode', "QR Code")
                ->linkToCrudAction('show_qrcode')
                ->setIcon('fa fa-download');

        return parent::configureActions($actions)
            ->remove(Crud::PAGE_INDEX, "delete")
            ->add(Crud::PAGE_INDEX, $passwordLink)
            ->add(Crud::PAGE_INDEX, $shortLink)
            ->add(Crud::PAGE_INDEX, $longLink)
            ->add(Crud::PAGE_INDEX, $qrCode)
            ;
    }
    
    #[AdminAction(routePath: '/{entityId}/activate_ruestzeit', routeName: 'activate_ruestzeit', methods: ['GET'])]
    public function activate_ruestzeit(AdminContext $context)
    {
        $ruestzeit = $context->getEntity()->getInstance();

        $this->requestStack->getSession()->set("current_ruestzeit", $ruestzeit->getId());

        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(RuestzeitCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl();
        
        return new RedirectResponse($url);

    }

    #[AdminAction(routePath: '/{entityId}/qrcode', routeName: 'ruestzeit_qrcode', methods: ['GET'])]
    public function show_qrcode(AdminContext $context, BuilderInterface $customQrCodeBuilder)
    {
        $ruestzeit = $context->getEntity()->getInstance();

        $result = $customQrCodeBuilder->build(
            data: "https://" . $_SERVER["HTTP_HOST"] . "/" . $ruestzeit->getForwarder(),
            labelText: "Anmeldung",
            size: 200,
            margin: 5
        );        
        
        $response = new QrCodeResponse($result);
        
        return $response;

    }

    public function configureAssets(Assets $assets): Assets
    {
        $assets->addAssetMapperEntry('quill-admin');

        return parent::configureAssets($assets); // TODO: Change the autogenerated stub
    }
        
    public function configureFields(string $pageName): iterable
    {

        if($pageName == Crud::PAGE_INDEX) {
            yield Field::new('customAction', '')
                ->setTemplatePath('admin/activate_ruestzeit.html.twig');         
            yield Field::new('linkAction', '')
                ->setTemplatePath('admin/link_ruestzeit.html.twig');         
        }
        yield FormField::addColumn(6);

        $field = BooleanField::new('registration_active', 'Anmeldung aktiv')
            ->setCustomOption('xls-width', 60);
            
        if ($pageName == Crud::PAGE_INDEX) {
            $field->setFormTypeOption('disabled', true);
        }      
        yield $field;      


        yield FormField::addColumn(6);
        
        yield TextField::new('forwarder', 'Weiterleitungs Kennung')
            ->setHelp(" (max. 12 Zeichen) Diese Kennung kann in Anmeldungen verwendet werden https://" . $_SERVER["HTTP_HOST"]."/(weiterleitungs-kennung)")
            ->setMaxLength(12);

        yield FormField::addColumn(6);

        yield TextField::new('title', 'Titel');

        yield FormField::addColumn(6);

        yield AssociationField::new('location', 'Ort')
            ->setCrudController(LocationCrudController::class);

        if ($pageName != Crud::PAGE_INDEX) {
            yield FormField::addColumn(12);

            yield QuillAdminField::new('description', 'Beschreibung')        
                ->setFormTypeOptions([
                    'quill_options' =>
                        QuillGroup::buildWithAllFields()
                ]);
        }

        if ($pageName == 'index') {
            yield IntegerField::new('memberCount');
        }

        yield FormField::addColumn(6);
        yield IntegerField::new('memberlimit', 'Teilnehmerlimit');

        if ($pageName != Crud::PAGE_INDEX) {
            yield FormField::addColumn(6);
            yield TextField::new('internalTitle', 'interne Bezeichnung')->setHelp('Angezeigt auf Unterschriftenliste');
        }

        yield FormField::addColumn(12);
        yield DateTimeField::new('registration_start', 'Anmeldestart')
            ->setTimezone("Europe/Berlin")
            ->setFormTypeOption('view_timezone', "Europe/Berlin");

        yield FormField::addColumn(6);
        yield UrlField::new('flyer_url', 'Flyer URL');

        yield FormField::addColumn(6);
        yield UrlField::new('image_url', 'Flyer Image URL');

        yield FormField::addColumn(6);
        yield DateField::new('date_from', 'Rüstzeit ab');

        yield FormField::addColumn(6);
        yield DateField::new('date_to', 'Rüstzeit bis');

        if ($pageName != Crud::PAGE_INDEX) {
            yield FormField::addColumn(2);
            yield BooleanField::new('show_location', 'Rüstzeitort anzeigen');

            yield FormField::addColumn(2);
            yield BooleanField::new('show_dates', 'Rüstzeitdatum anzeigen');

            yield FormField::addColumn(2);
            yield BooleanField::new('ask_schoolclass', 'Schulklasse erfragen');

            yield FormField::addColumn(2);
            yield BooleanField::new('show_room_request', 'Raumwunsch angeben');

            yield FormField::addColumn(2);
            yield BooleanField::new('show_referer', '"Eingeladen von" erfragen');

            yield FormField::addColumn(6);

            yield TextField::new('aktenzeichen', 'Aktenzeichen Zwickau');            

            yield FormField::addColumn(6);

            yield TextField::new('password', 'Passwort')
                ->setHelp('Mit diesem Passwort, kann die Anmeldesperre umgangen werden. a-z, A-Z, 0-9 und Sonderzeichen - _');

            yield ColorField::new('admincolor', 'Admin Farbe')
                ->setHelp('Diese Farbe hilft dabei, in der Verwaltungsoberfläche zu erkennen, welche Rüstzeit aktuell geöffnet ist');
        }
    }

    public function createEntity(string $entityFqcn) {
        $entity = new Ruestzeit();
        $entity->setAdmin($this->getUser());

        return $entity;
    }    
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryBuilder
            ->andWhere('entity.admin = :user')->setParameter(':user', $this->getUser()->getId());

        return $queryBuilder;
    }
}
