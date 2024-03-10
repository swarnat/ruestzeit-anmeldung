<?php

namespace App\Controller;

use App\Entity\Anmeldung;
use App\Form\AnmeldungType;
use App\Repository\RuestzeitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class RuestzeitController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/', name: 'homepage')]
    public function index(Request $request, Environment $twig, RuestzeitRepository $ruestzeitRepository): Response
    {
        $ruestzeit = $ruestzeitRepository->findOneBy([]);

        $anmeldung = new Anmeldung();
        $form = $this->createForm(AnmeldungType::class, $anmeldung);

        $form->handleRequest($request);

        $formView = $form->createView();

        if ($form->isSubmitted() && $form->isValid()) {
            // $anmeldung->setRuestzeit($ruestzeit);

            $this->entityManager->persist($anmeldung);
            $this->entityManager->flush();

            flash()->addSuccess('Die Anmeldung wurde erfolgreich gespeichert. Vielen Dank!', 'Erfolgreich');

            return $this->redirectToRoute('homepage');
        }

        return new Response($twig->render('ruestzeit/index.html.twig', [
            'ruestzeit' => $ruestzeit,
            'form' => !empty($formView) ? $formView : [], 
        ]));
    }
}
