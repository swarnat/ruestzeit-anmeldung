<?php

namespace App\Controller;

use App\Entity\Anmeldung;
use App\Enum\AnmeldungStatus;
use App\Enum\PersonenTyp;
use App\Form\AnmeldungType;
use App\Repository\RuestzeitRepository;
use App\Service\PostalcodeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class RuestzeitController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PostalcodeService $postalcodeService
    ) {
    }

    #[Route('/', name: 'homepage')]
    public function index(Request $request, Environment $twig, RuestzeitRepository $ruestzeitRepository, MailerInterface $mailer): Response
    {
        $ruestzeit = $ruestzeitRepository->findOneBy([]);

        $allowRegistration = false;

        if ($request->get('pw', null) !== null && 
            $ruestzeit->getPassword() != '' && 
            $request->get('pw', null) === $ruestzeit->getPassword()
        ) {
            $allowRegistration = true;
        }

        $anmeldung = new Anmeldung();
        $form = $this->createForm(AnmeldungType::class, $anmeldung);

        $form->handleRequest($request);

        $formView = $form->createView();

        if ($form->isSubmitted() && $form->isValid()) {
            $repeatProcess = !empty($request->get('repeat_process'));
            $anmeldungData = $request->get('anmeldung');
            $timingValue = (int)$request->get('timing');

            if(!empty($anmeldungData['agefield'])) {
                $captcha = false;
            } elseif($anmeldungData['email_repeat'] != (($timingValue * 3) / 2) . '@example.com') {
                $captcha = false;
            } else {
                $captcha = true;
            }
            
            if($captcha) {
                $anmeldung->setRuestzeit($ruestzeit);

                $query = $this->entityManager->createQueryBuilder('anmeldung');
                $query->from(Anmeldung::class, 'anmeldung');
                $query->select('MAX(anmeldung.registrationPosition) + 1 max_position');
                $query->where('anmeldung.ruestzeit = :ruestzeit')->setParameter('ruestzeit', $ruestzeit);

                $result = $query->getQuery()->getSingleResult();

                $anmeldung->setRegistrationPosition($result['max_position']);

                if ($ruestzeit->isFull()) {
                    $anmeldung->setStatus(AnmeldungStatus::WAITLIST);
                } else {
                    $anmeldung->setStatus(AnmeldungStatus::ACTIVE);
                }

                $postalcodeData = $this->postalcodeService->getPostalcodeData("DE", $anmeldung->getPostalcode());
                if (!empty($postalcodeData)) {
                    $anmeldung->setLandkreis($postalcodeData["region"]);
                }
                
                $anmeldung->setPersonenTyp(PersonenTyp::TEILNEHMER);

                $this->entityManager->persist($anmeldung);
                $this->entityManager->flush();

                $email = (new TemplatedEmail())
                    ->from('no-reply@kirche-hohndorf.de')
                    ->to($ruestzeit->getAdmin()->getEmail())
                    ->htmlTemplate('emails/anmeldung.html.twig')
                    ->locale('de')
                    ->subject('[' . $ruestzeit->getTitle() . '] Anmeldung ' . $anmeldung->getLastname() . ', ' . $anmeldung->getFirstname() . ' [' . $anmeldung->getStatus()->value . ']')
                    ->context([
                        'anmeldung' => $anmeldung,
                    ]);

                try {
                    $debug = $mailer->send($email);
                    var_dump($debug);
                } catch (TransportExceptionInterface $e) {
                    var_dump($e);
                    // some error prevented the email sending; display an
                    // error message or try to resend the message
                }

                $emailAddress = $anmeldung->getEmail();

                if(!empty($emailAddress)) {
                    $email = (new TemplatedEmail())
                        ->from('no-reply@kirche-hohndorf.de')
                        ->to($emailAddress)
                        ->htmlTemplate('emails/confirmation.html.twig')
                        ->locale('de')
                        ->subject('[' . $ruestzeit->getTitle() . '] Bestätigung der Anmeldung')
                        ->context([
                            'anmeldung' => $anmeldung,
                        ]);

                    try {
                        $debug = $mailer->send($email);
                        var_dump($debug);
                    } catch (TransportExceptionInterface $e) {
                        var_dump($e);
                        // some error prevented the email sending; display an
                        // error message or try to resend the message
                    }
                }

                flash()->addSuccess('Die Anmeldung wurde erfolgreich gespeichert.<br/>Vielen Dank!', 'Erfolgreich', [
                    'timeout' => 30000
                ]);

                if($repeatProcess) {
                    $nextAnmeldung = new Anmeldung();
                    $nextAnmeldung->setAddress($anmeldung->getAddress());
                    $nextAnmeldung->setPostalcode($anmeldung->getPostalcode());
                    $nextAnmeldung->setCity($anmeldung->getCity());
                    $nextAnmeldung->setPhone($anmeldung->getPhone());
                    $nextAnmeldung->setEmail($anmeldung->getEmail());

                    $form = $this->createForm(AnmeldungType::class, $nextAnmeldung);
                    // $request->request->set('firstname', '');
                    // $request->request->set('lastname', '');
                    // $form->handleRequest($request);            
                    $formView = $form->createView();                    
                    
                    return new Response($twig->render('ruestzeit/index.html.twig', [
                        'ruestzeit' => $ruestzeit,
                        'allowRegistration' => $allowRegistration,
                        'form' => !empty($formView) ? $formView : [],
                    ]));
                }

                if ($allowRegistration) {
                    return $this->redirectToRoute('homepage', ["pw" => $ruestzeit->getPassword()]);
                } else {
                    return $this->redirectToRoute('homepage');
                }
            } else {
                flash()->addWarning('Fehler bei der Verarbeitung', 'Fehler', [
                    'timeout' => 30000
                ]);
            }
        }

        return new Response($twig->render('ruestzeit/index.html.twig', [
            'ruestzeit' => $ruestzeit,
            'allowRegistration' => $allowRegistration,
            'form' => !empty($formView) ? $formView : [],
        ]));
    }
}
