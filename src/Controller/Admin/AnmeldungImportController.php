<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AnmeldungImportController extends AbstractController
{
    #[Route('/anmeldung/import', name: 'app_anmeldung_import')]
    public function index(): Response
    {
        return $this->render('anmeldung_import/index.html.twig', [
            'controller_name' => 'AnmeldungImportController',
        ]);
    }
}
