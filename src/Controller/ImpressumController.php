<?php

namespace App\Controller;

use App\Entity\Anmeldung;
use App\Enum\AnmeldungStatus;
use App\Form\AnmeldungType;
use App\Repository\RuestzeitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class ImpressumController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/impressum', name: 'impressum')]
    public function index(Request $request, Environment $twig, RuestzeitRepository $ruestzeitRepository): Response
    {
        
        return new Response($twig->render('impressum.html.twig'));
    }

}
