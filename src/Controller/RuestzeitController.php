<?php

namespace App\Controller;

use App\Entity\Anmeldung;
use App\Entity\CustomFieldAnswer;
use App\Enum\AnmeldungStatus;
use App\Enum\CustomFieldType;
use App\Enum\PersonenTyp;
use App\Form\AnmeldungType;
use App\Generator\CurrentRuestzeitGenerator;
use App\Repository\RuestzeitRepository;
use App\Service\PostalcodeService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class RuestzeitController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PostalcodeService $postalcodeService,
        private CurrentRuestzeitGenerator $currentRuestzeitGenerator,
        private KernelInterface $appKernel,
        private LoggerInterface $logger
    ) {
    }

    public function show(Request $request, Environment $twig, RuestzeitRepository $ruestzeitRepository, MailerInterface $mailer, String $ruestzeit_id): Response
    {
        $ruestzeit = $ruestzeitRepository->findOneBy([
            "forwarder" => $ruestzeit_id
        ]);
        $initialcToken = "000";

        if (!empty($ruestzeit)) {
            return new RedirectResponse("https://" . $ruestzeit->getDomain() . "/" . $ruestzeit->getSlug());
        }

        $ruestzeit = $ruestzeitRepository->findOneBy([
            "slug" => $ruestzeit_id
        ]);

        if (empty($ruestzeit)) {
            return new Response($twig->render('ruestzeit/not-found.html.twig', []));
        }

        $this->currentRuestzeitGenerator->set($ruestzeit);

        $allowRegistration = false;

        if (
            $request->get('pw', null) !== null &&
            $ruestzeit->getPassword() != '' &&
            $request->get('pw', null) === $ruestzeit->getPassword()
        ) {
            $allowRegistration = true;
        }

        $anmeldung = new Anmeldung();
        $form = $this->createForm(AnmeldungType::class, $anmeldung);

        $form->handleRequest($request);

        $formView = $form->createView();

        $error = false;

        if ($form->isSubmitted() && $form->isValid()) {
            if (
                $ruestzeit->isRegistrationActive() === false
            ) {
                $error = true;

                $this->logger->error('Registration received, but registration is disabled');
            }

            if (empty($error)) {
                $repeatProcess = !empty($request->get('repeat_process'));
                $ctoken = $request->get('ctoken');
                $anmeldungData = $request->get('anmeldung');
                $timingValue = (int)$request->get('timing');

                if (!empty($anmeldungData['agefield'])) {
                    $captcha = false;
                } elseif ($anmeldungData['email_repeat'] != (($timingValue * 3) / 2) . '@example.com') {
                    $captcha = false;
                } elseif(strpos($ctoken, "0") !== false) {
                    $captcha = false;
                } else {
                    $captcha = true;
                }

                if ($captcha) {
                    $anmeldung->setRuestzeit($ruestzeit);

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

                    // Handle custom field answers
                    foreach ($ruestzeit->getCustomFields() as $customField) {
                        $fieldName = 'custom_field_' . $customField->getId();
                        $value = $request->get('anmeldung')[$fieldName] ?? null;
                        
                        if ($value !== null) {
                            $answer = new CustomFieldAnswer();
                            $answer->setCustomField($customField);
                            $answer->setAnmeldung($anmeldung);
                            
                            // Handle different field types
                            switch ($customField->getType()) {
                                case CustomFieldType::CHECKBOX:
                                    $answer->setValue(json_encode($value, JSON_UNESCAPED_UNICODE));
                                    break;
                                case CustomFieldType::DATE:
                                    // Convert date from dd.mm.yyyy to Y-m-d
                                    if ($value) {
                                        $date = \DateTime::createFromFormat('d.m.Y', $value);
                                        if ($date) {
                                            $answer->setValue($date->format('Y-m-d'));
                                        }
                                    }
                                    break;
                                default:
                                    $answer->setValue((string)$value);
                            }
                            
                            $this->entityManager->persist($answer);
                        }
                    }

                    $this->entityManager->persist($anmeldung);
                    $this->entityManager->flush();

                    $email = (new TemplatedEmail())
                        ->to($ruestzeit->getAdmin()->getEmail())
                        ->htmlTemplate('emails/anmeldung.html.twig')
                        ->locale('de')
                        ->subject('[' . $ruestzeit->getTitle() . '] Anmeldung ' . $anmeldung->getLastname() . ', ' . $anmeldung->getFirstname() . ' [' . $anmeldung->getStatus()->value . ']')
                        ->context([
                            'anmeldung' => $anmeldung,
                        ]);

                    try {
                        $debug = $mailer->send($email);
                    } catch (TransportExceptionInterface $e) {
                        // some error prevented the email sending; display an
                        // error message or try to resend the message
                    }

                    $emailAddress = $anmeldung->getEmail();

                    if (!empty($emailAddress)) {
                        try {
                            $email = (new TemplatedEmail())
                                ->to($emailAddress)
                                ->htmlTemplate('emails/confirmation.html.twig')
                                ->locale('de')
                                ->subject('[' . $ruestzeit->getTitle() . '] Bestätigung der Anmeldung')
                                ->context([
                                    'anmeldung' => $anmeldung,
                                ]);

                            $debug = $mailer->send($email);
                        } catch (\Exception $e) {
                            $this->logger->error($e->getMessage());
                            // some error prevented the email sending; display an
                            // error message or try to resend the message
                        }
                    }

                    toastr()
                        ->positionClass('toast-top-center toast-full-width')
                        ->timeOut(10000)
                        ->success('Die Anmeldung wurde erfolgreich gespeichert.<br/>Vielen Dank!', [], "Erfolgreich");

                    if ($repeatProcess) {
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
                        $initialcToken = "222";
                        
                        return new Response($twig->render('ruestzeit/index.html.twig', [
                            'ruestzeit' => $ruestzeit,
                            'initial_ctoken' => $initialcToken,

                            'allowRegistration' => $allowRegistration,
                            'form' => !empty($formView) ? $formView : [],
                        ]));
                    }

                    if ($allowRegistration) {
                        return $this->redirectToRoute('ruestzeit', ["ruestzeit_id" => $ruestzeit->getSlug(), "pw" => $ruestzeit->getPassword()]);
                    } else {
                        return $this->redirectToRoute('ruestzeit', ["ruestzeit_id" => $ruestzeit->getSlug(), ]);
                    }
                } else {
                    $this->logger->notice("Captcha Check not success");

                    $initialcToken = "222";
                    toastr()
                        ->positionClass('toast-top-center toast-full-width')
                        ->timeOut(10000)
                        ->error('Fehler bei der Verarbeitung. Bitte erneut versuchen', [], "Fehler");
                }
            } else {
                $this->logger->notice("Error with data during registration");

                $initialcToken = "222";
                toastr()
                    ->positionClass('toast-top-center toast-full-width')
                    ->timeOut(10000)
                    ->error('Fehler bei der Verarbeitung. Bitte erneut versuchen', [], "Fehler");
            }
        }

        return new Response($twig->render('ruestzeit/index.html.twig', [
            'ruestzeit' => $ruestzeit,
            'allowRegistration' => $allowRegistration,
            'initial_ctoken' => $initialcToken,
            'canonical' => $ruestzeit->getUrl(),
            'form' => !empty($formView) ? $formView : [],
        ]));
    }
}
