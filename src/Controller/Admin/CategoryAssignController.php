<?php

namespace App\Controller\Admin;

use App\Entity\Anmeldung;
use App\Entity\Category;
use App\Enum\AnmeldungStatus;
use App\Repository\AnmeldungRepository;
use App\Repository\CategoryRepository;
use App\Repository\RuestzeitRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;


use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CategoryAssignController extends AbstractController
{
    #[Route('/category/assignment-ui', name: 'teilnehmer_cat_assignments')]
    public function index(
        Request $request,
        CategoryRepository $categoryRepository,
        RuestzeitRepository $ruestzeitRepository
    ): Response
    {
        $categories = $categoryRepository->findBy(["id" => $request->get("batchActionEntityIds", [])]);

        $ruestzeit = $ruestzeitRepository->findOneBy([]);

        $anmeldungen = $ruestzeit->getActiveAnmeldungen();

        return $this->render('categories/assign.html.twig', [
            'controller_name' => 'CategoryAssignController',
            "categories" => $categories,
            "anmeldungen" => $anmeldungen
        ]);
    }

    #[Route('/admin/category/store', name: 'teilnehmer_cat_assign', methods:["POST"])]
    public function store(
        Request $request,
        CategoryRepository $categoryRepository,
        AnmeldungRepository $anmeldungRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $parameters = json_decode($request->getContent(), true);

        $category = $categoryRepository->findOneBy(["id" => $parameters["category_id"]]);
        $anmeldung = $anmeldungRepository->findOneBy(["id" => $parameters["anmeldung_id"]]);

        if($parameters["value"] == "1") {
            $anmeldung->addCategory($category);
        } else {
            $anmeldung->removeCategory($category);            
        }

        $entityManager->persist($anmeldung);
        $entityManager->flush();
        
     
        return new JsonResponse([]);
    }

   
}
