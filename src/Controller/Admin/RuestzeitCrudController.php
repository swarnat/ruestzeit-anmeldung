<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\Ruestzeit;
use App\Service\CodeGenerator;
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
use App\Service\S3FileUploader;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
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
        protected EntityManagerInterface $entityManager,
        protected CodeGenerator $codeGenerator,
        protected S3FileUploader $s3FileUploader,
        private string $domainList,
    ) {}

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

        $activateAction = Action::new('ruestzeit_activate', "Ruestzeit wechseln")
            ->linkToCrudAction('activate_ruestzeit')
            ->setIcon('fa fa-download');

        $qrCode = Action::new('show_qrcode', "QR Code")
            ->linkToCrudAction('show_qrcode')
            ->setIcon('fa fa-download');

        $shareAction = Action::new('share', 'Freigeben')
            ->linkToUrl(function (Ruestzeit $ruestzeit) {
                return $this->container->get(AdminUrlGenerator::class)
                    ->setRoute('admin_ruestzeit_share', ['id' => $ruestzeit->getId()])
                    ->generateUrl();
            })
            ->setIcon('fa fa-share-alt')
            ->displayIf(function (Ruestzeit $ruestzeit) {
                return $ruestzeit->getAdmin() === $this->getUser();
            });

        return parent::configureActions($actions)
            ->remove(Crud::PAGE_INDEX, "delete")
            ->add(Crud::PAGE_INDEX, $passwordLink)
            ->add(Crud::PAGE_INDEX, $shortLink)
            ->add(Crud::PAGE_INDEX, $longLink)
            ->add(Crud::PAGE_INDEX, $activateAction)
            ->add(Crud::PAGE_INDEX, $qrCode)
            ->add(Crud::PAGE_INDEX, $shareAction)
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
            data: "https://" . $ruestzeit->getDomain() . "/" . $ruestzeit->getForwarder(),
            labelText: "Anmeldung",
            size: 200,
            margin: 5
        );

        $response = new QrCodeResponse($result);

        return $response;
    }

    public function configureAssets(Assets $assets): Assets
    {
        // $assets->addAssetMapperEntry('quill-admin');

        return parent::configureAssets($assets); // TODO: Change the autogenerated stub
    }

    public function configureFields(string $pageName): iterable
    {
        $domains = explode(",", $this->domainList);
        $context = $this->getContext();

        if ($pageName == Crud::PAGE_INDEX) {
            yield Field::new('customAction', '')
                ->setTemplatePath('admin/activate_ruestzeit.html.twig');
            yield Field::new('linkAction', '')
                ->setTemplatePath('admin/link_ruestzeit.html.twig');
        }
        yield FormField::addColumn(12);

        yield FormField::addPanel("")->setColumns(6);

        $field = BooleanField::new('registration_active', 'Anmeldung aktiv')
            ->setColumns(4)
            ->setCustomOption('xls-width', 60);

        if ($pageName == Crud::PAGE_INDEX) {
            $field->setFormTypeOption('disabled', true);
        }
        yield $field;

        yield TextField::new('title', 'Titel')
            ->setColumns(5);

        yield IntegerField::new('memberlimit', 'Teilnehmerlimit')
            ->setColumns(3);


        yield FormField::addColumn(6);

        yield FormField::addPanel('Grunddaten')->setColumns(6);

        yield AssociationField::new('location', 'Ort')
            ->setCrudController(LocationCrudController::class);

        // yield FormField::addColumn(3);
        yield DateField::new('date_from', 'Rüstzeit ab')->setColumns(4);

        // yield FormField::addColumn(3);
        yield DateField::new('date_to', 'Rüstzeit bis')->setColumns(4);

        yield DateTimeField::new('registration_start', 'Anmeldestart')
            ->setColumns(4)
            ->setTimezone("Europe/Berlin")
            ->setFormTypeOption('view_timezone', "Europe/Berlin");


        yield FormField::addColumn(6);

        yield FormField::addPanel('Internetadresse')->setColumns(6);

        if ($pageName === Crud::PAGE_EDIT) {
            $ruestzeit = $context->getEntity()->getInstance();
            $currentDomain = $ruestzeit->getDomain();
        } else {
            $currentDomain = $domains[0];
        }
        if (empty($currentDomain)) {
            $currentDomain = $domains[0];
        }
        yield ChoiceField::new('domain')
            ->setColumns(6)
            ->setChoices(array_combine($domains, $domains))
            ->setFormTypeOption('data', $currentDomain);

        yield TextField::new('forwarder', 'Weiterleitungs Kennung')
            ->setColumns(6)
            ->setHelp(" (max. 12 Zeichen) Diese Kennung kann in Anmeldungen verwendet werden https://" . $_SERVER["HTTP_HOST"] . "/(weiterleitungs-kennung)")
            ->setMaxLength(12);

        yield FormField::addColumn(6);


        if ($pageName != Crud::PAGE_INDEX) {
            yield FormField::addColumn(12);

            yield QuillAdminField::new('description', 'Beschreibung')
                ->setFormTypeOptions([
                    'quill_options' =>
                    QuillGroup::buildWithAllFields()
                ]);
        }

        if ($pageName == 'index') {
            yield IntegerField::new('memberCount', "Anmeldungen");
        }

        yield FormField::addColumn(12);

        yield FormField::addPanel('Werbematerial')->setColumns(12);

        if ($pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_NEW) {
            yield Field::new('flyerFile', 'Flyer')
                ->setColumns(6)
                ->setFormType(FileType::class)
                ->setFormTypeOption('required', false);
        }


        if ($pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_NEW) {
            yield Field::new('imageFile', 'Flyer Vorschaubild')
                ->setColumns(6)
                ->setFormType(FileType::class)
                ->setFormTypeOption('required', false);
        }

        if ($pageName !== Crud::PAGE_NEW) {
            yield TextField::new('flyerUrl', 'Aktueller Flyer')
                ->setColumns(6)
                ->setTemplatePath('admin/field/s3_file.html.twig');
        }

        // yield FormField::addColumn(6);


        if ($pageName !== Crud::PAGE_NEW) {
            yield TextField::new('imageUrl', 'Aktuelles Vorschaubild')
                ->setColumns(6)
                ->setTemplatePath('admin/field/s3_file.html.twig');
        }


        if ($pageName != Crud::PAGE_INDEX) {
            yield FormField::addColumn(6);
            yield TextField::new('internalTitle', 'interne Bezeichnung')->setHelp('Angezeigt auf Unterschriftenliste');
        }

        yield TextField::new('aktenzeichen', 'Aktenzeichen Zwickau')->setColumns(2);

        yield FormField::addPanel('Optionen');

        if ($pageName != Crud::PAGE_INDEX) {
            yield BooleanField::new('show_location', 'Rüstzeitort anzeigen')->setColumns(4);

            yield BooleanField::new('show_dates', 'Rüstzeitdatum anzeigen')->setColumns(4);

            yield BooleanField::new('ask_schoolclass', 'Schulklasse erfragen')->setColumns(4);

            yield BooleanField::new('show_room_request', 'Raumwunsch angeben')->setColumns(4);

            yield BooleanField::new('show_referer', '"Eingeladen von" erfragen')->setColumns(4);

            yield BooleanField::new('show_roommate', 'Zimmernachbar erfragen')->setColumns(4);

            yield BooleanField::new('show_mealtype', 'Verpflegung erfragen')->setColumns(4);

            // yield FormField::addColumn(6);


            yield FormField::addColumn(6);

            yield FormField::addPanel('Verwaltung');


            yield TextField::new('password', 'Passwort')
                ->setColumns(6)
                ->setHelp('Mit diesem Passwort, kann die Anmeldesperre umgangen werden. a-z, A-Z, 0-9 und Sonderzeichen - _');

            yield ColorField::new('admincolor', 'Admin Farbe')
                ->setColumns(6)
                ->setHelp('Diese Farbe hilft dabei, in der Verwaltungsoberfläche zu erkennen, welche Rüstzeit aktuell geöffnet ist');

            yield TextField::new('additional_question1', 'Zusätzliche Frage 1')
                ->setRequired(false)
                ->setColumns(12);
        }
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->uploadFiles($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->uploadFiles($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    private function uploadFiles($entity): void
    {
        if ($entity->getFlyerFile()) {
            $key = $this->s3FileUploader->upload(
                $entity->getFlyerFile(),
                'flyers'
            );
            $entity->setFlyerUrl($this->s3FileUploader->getPublicUrl($key));
        }

        if ($entity->getImageFile()) {
            $key = $this->s3FileUploader->upload(
                $entity->getImageFile(),
                'images'
            );
            $entity->setImageUrl($this->s3FileUploader->getPublicUrl($key));
        }
    }

    public function createEntity(string $entityFqcn)
    {
        $entity = new Ruestzeit();
        // var_dump($entity->getDescription());
        // exit();
        $entity->setDescription("Hier die Beschreibung der Rüstzeit einfügen.");

        do {
            $code = strtoupper($this->codeGenerator->generate(4)) . date("Y");
            $existing = $this->entityManager->getRepository(Ruestzeit::class)->findOneBy(['forwarder' => $code]);
        } while ($existing);

        $entity->setForwarder($code);
        $entity->setAdmin($this->getUser());

        return $entity;
    }
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryBuilder
            ->leftJoin('entity.sharedAdmins', 'sa')
            ->andWhere('entity.admin = :user OR sa = :user')
            ->setParameter(':user', $this->getUser());

        return $queryBuilder;
    }
}
