<?php

namespace App\Controller;

use App\Repository\RuestzeitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class RuestzeitController extends AbstractController
{
    #[Route('/', name: 'homepage')]
       public function index(Environment $twig, RuestzeitRepository $ruestzeitRepository): Response
    {
        return new Response($twig->render('ruestzeit/index.html.twig', [
            'ruestzeit' => $ruestzeitRepository->findOneBy([]),
        ]));
    }
}
