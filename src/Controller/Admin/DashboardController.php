<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\Anmeldung;
use App\Entity\Category;
use App\Entity\CustomField;
use App\Entity\Landkreis;
use App\Entity\Location;
use App\Entity\Ruestzeit;
use App\Generator\CurrentRuestzeitGenerator;
use App\Repository\RuestzeitRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private RuestzeitRepository $ruestzeitRepository,
        private CurrentRuestzeitGenerator $currentRuestzeitGenerator
        )
    {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // $ruestzeit = $this->ruestzeitRepository->findOneBy([
        //     // "slug" => $ruestzeit_id
        // ]);

       
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);
        $url = $routeBuilder->setController(RuestzeitCrudController::class)->generateUrl();
    
        return $this->redirect($url);
        

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

        
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setLocales([ ])
            ->renderContentMaximized()
            ->setTitle('Rüstzeit Anmeldungen');
    }

    public function configureMenuItems(): iterable
    {
        $ruestzeit = $this->currentRuestzeitGenerator->get();

        // yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);

        yield MenuItem::linkToUrl('Homepage', 'fas fa-home', '/' . $ruestzeit->getSlug());
        // yield MenuItem::linktoRoute('Homepage', 'fas fa-home', 'homepage');

        yield MenuItem::section('Rüstzeiten');
        yield MenuItem::linkToCrud('Orte', 'fas fa-map-marker-alt', Location::class);
        yield MenuItem::linkToCrud('Rüstzeiten', 'fas fa-map-marker-alt', Ruestzeit::class);

        yield MenuItem::section('Anmeldungen');

        yield MenuItem::linkToCrud('Teilnehmer', 'fas fa-file-import', Anmeldung::class)
            ->setController(AnmeldungCrudController::class)
            ->setBadge(!empty($ruestzeit) ? $ruestzeit->getMemberCount() . '/' . $ruestzeit->getMemberlimit() : 0 );
        ;

        

        yield MenuItem::linkToCrud('Warteliste', 'fas fa-hourglass-half', Anmeldung::class)
            ->setController(WaitinglistCrudController::class)
            ->setBadge(!empty($ruestzeit) ? count($ruestzeit->getWaitlistAnmeldungen()) : 0);
        ;

        yield MenuItem::linkToRoute(
            label: "Import", 
            icon: 'fas fa-upload',
            routeName: 'app_anmeldung_import'
        );

        yield MenuItem::linkToRoute("Unterschriften", 'fas fa-upload', 'app_anmeldung_unterschriften');
        
        yield MenuItem::section('Verwaltung');

        yield MenuItem::linkToRoute("Bezeichnungen", 'fas fa-upload', 'ruestzeit_label_overwrite');

        yield MenuItem::linkToCrud('Landkreise', 'fas fa-flag', Landkreis::class);
        
        yield MenuItem::linkToCrud('Kategorie', 'fas fa-flag', Category::class);

        yield MenuItem::linkToCrud('Zusatzfelder', 'fas fa-list-check', CustomField::class)
            ->setController(CustomFieldCrudController::class);
        
        yield MenuItem::linkToCrud('Benutzer', 'fas fa-person', Admin::class)
                ->setEntityId($this->getUser()->getId())
                ->setAction('edit');
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
                // ->addWebpackEncoreEntry('Bulma')
                // ->addWebpackEncoreEntry('admin')
                // ->addWebpackEncoreEntry('app')
                ;
    }     
}
