<?php

namespace App\Controller\Admin;

use App\Entity\Config;
use App\Repository\ConfigRepository;
use Doctrine\DBAL\Types\TextType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType as TypeTextType;

class SettingsController extends AbstractController
{
    private const IMPRINT_KEY = 'imprint';
    private const IMPRINT_MAIL_KEY = 'imprint_mail';

    public function __construct(
        private ConfigRepository $configRepository,
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
    }


    #[Route('/admin/settings', name: 'admin_settings')]
    public function index(Request $request): Response
    {
        $imprintValue = $this->configRepository->getValue(self::IMPRINT_KEY) ?? '';
        $imprintMailValue = $this->configRepository->getValue(self::IMPRINT_MAIL_KEY) ?? '';
        
        $form = $this->createFormBuilder()
            ->add(''.self::IMPRINT_KEY.'', TextareaType::class, [
                'label' => 'Impressum auf Homepage',
                'required' => false,
                'data' => $imprintValue,
                'attr' => [
                    'rows' => 10,
                    'class' => 'form-control'
                ],
                'help' => 'HTML ist erlaubt. Dieses Impressum wird auf der Webseite angezeigt.'
            ])
            ->add(''.self::IMPRINT_MAIL_KEY.'', TypeTextType::class, [
                'label' => 'Impressum in E-Mail',
                'required' => false,
                'data' => $imprintMailValue,
                'attr' => [
                    'class' => 'form-control'
                ],
                'help' => 'HTML ist erlaubt. Dieses Impressum wird in E-Mails in der Fußzeile integriert'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Speichern',
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->getForm();
            
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            // Save imprint
            $this->configRepository->setValue(self::IMPRINT_KEY, $data[self::IMPRINT_KEY]);
            $this->configRepository->setValue(self::IMPRINT_MAIL_KEY, $data[self::IMPRINT_MAIL_KEY]);
            
            $this->addFlash('success', 'Einstellungen wurden erfolgreich gespeichert.');
            
            // Redirect to the same page to prevent form resubmission
            return $this->redirect($this->adminUrlGenerator
                ->setRoute('admin_settings')
                ->generateUrl());
        }
        
        return $this->render('admin/settings.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}