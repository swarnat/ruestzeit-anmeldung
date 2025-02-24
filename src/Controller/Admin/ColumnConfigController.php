<?php

namespace App\Controller\Admin;

use App\Entity\UserColumnConfig;
use App\Generator\CurrentRuestzeitGenerator;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ColumnConfigController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CurrentRuestzeitGenerator $currentRuestzeitGenerator,
        private AdminUrlGenerator $adminUrlGenerator,
        private TranslatorInterface $translator,
    ) {}

    #[Route('/admin/anmeldungen-fields', name: 'admin_anmeldung_column_config')]
    public function index(): Response
    {
        $currentRuestzeit = $this->currentRuestzeitGenerator->get();
        $config = $this->entityManager->getRepository(UserColumnConfig::class)
            ->findForUserAndRuestzeit(
                $this->getUser(),
                $currentRuestzeit
            );

        // Define the base structure
        $allAvailableColumns = [
            'CORE_INFORMATION' => [
                'firstname' => 'Vorname',
                'lastname' => 'Nachname',
                'birthdate' => 'Geburtstag',
                'age' => 'Alter',
                'personenTyp' => 'Typ',
                'registrationPosition' => 'Reg.Position',
                'status' => 'Status',
            ],
            'CONTACT_INFORMATION' => [
                'email' => 'E-Mail',
                'phone' => 'Telefon',
                'address' => 'Adresse',
                'postalcode' => 'PLZ',
                'city' => 'Stadt',
                'landkreis' => 'Landkreis'
            ],
            'PAYMENT_INFORMATION' => [
                'prepayment_done' => 'Anzahlung',
                'payment_done' => 'Zahlung ok'
            ],
            'OTHER_INFORMATION' => [
                'schoolclass' => 'Schulklasse',                
                'roomnumber' => 'Raumnummer',
                'roommate' => 'Zimmerpartner',
                'roomRequest' => 'Raumwunsch',
                'mealtype' => 'Verpflegung',

                'createdAt' => 'Erstellt am',
                'notes' => 'Anmerkungen',
                'categories' => 'Kategorien',
            ],
            "CUSTOM_INFORMATION" => []
        ];

        $customFields = $currentRuestzeit->getCustomFields();
        foreach($customFields as $customField) {
            $allAvailableColumns["CUSTOM_INFORMATION"]["customfieldanswer_" . $customField->getId()] = $customField->getTitle();
        }

        if(!empty($config)) {
            $configuredFields = $config->getColumns();
        } else {
            $configuredFields = [
                [
                    "field" => "lastname",
                    "enabled" => true,
                    "order" => 0,
                ],
                [
                    "field" => "firstname",
                    "enabled" => true,
                    "order" => 1,
                ],
                [
                    "field" => "registrationPosition",
                    "enabled" => true,
                    "order" => 2,
                ],
                [
                    "field" => "age",
                    "enabled" => true,
                    "order" => 2,
                ],
                [
                    "field" => "mealtype",
                    "enabled" => true,
                    "order" => 3,
                ],
                [
                    "field" => "roomRequest",
                    "enabled" => true,
                    "order" => 4,
                ],
                [
                    "field" => "prepayment_done",
                    "enabled" => true,
                    "order" => 5,
                ],
                [
                    "field" => "payment_done",
                    "enabled" => true,
                    "order" => 6,
                ],
                [
                    "field" => "createdAt",
                    "enabled" => true,
                    "order" => 7,
                ],
            ];            
        }

        // Create a mapping of field to order from configured fields
        $fieldOrderMap = [];
        foreach ($configuredFields as $field) {
            $fieldMap[$field['field']] = [
                "order" => $field['order'],
                "enabled" => $field["enabled"],
            ];
        }

        $flatAvailableColumns = [];
        // Sort fields within each group based on configured order
        foreach ($allAvailableColumns as $group => &$fields) {
            foreach($fields as $fieldName => $label) {
                $flatColumns[] = [
                    "group" => \Symfony\Component\Translation\t($group, [], 'messages')->trans($this->translator),
                    "field" => $fieldName,
                    "label" => $label,
                    "enabled" => isset($fieldMap[$fieldName]) ? $fieldMap[$fieldName]["enabled"] : false,
                    "order" => isset($fieldMap[$fieldName]) ? $fieldMap[$fieldName]["order"] : 100
                ];
            }
        }
        // dump($flatColumns);exit();
            // Create temporary array with field => order pairs
            // $tempFields = [];
            // foreach ($fields as $field => $label) {
            //     $order = $fieldOrderMap[$field] ?? PHP_INT_MAX; // Use max value for unconfigured fields
            //     $tempFields[$field] = ['label' => $label, 'order' => $order];
            // }

            // Sort by order
        uasort($flatColumns, fn($a, $b) => $a['order'] <=> $b['order']);

            // // Rebuild the fields array maintaining the sorted order
            // $fields = array_combine(
            //     array_keys($tempFields),
            //     array_column($tempFields, 'label')
            // );
        // }
        // unset($fields); // Break reference to last element


        return $this->render('admin/column_config/index.html.twig', [
            // 'configuredFields' => $configuredFields,
            'flatColumns' => $flatColumns,
            'currentRuestzeit' => $this->currentRuestzeitGenerator->get()
        ]);
    }

    #[Route('/admin/anmeldung/column-config/save', name: 'admin_anmeldung_column_config_save', methods: ['POST'])]
    public function save(Request $request): Response
    {
        $selectedColumns = $request->request->all('columns');
        $availableColumns = $request->request->all('available_columns');
        
        $config = $this->entityManager->getRepository(UserColumnConfig::class)
            ->findForUserAndRuestzeit(
                $this->getUser(),
                $this->currentRuestzeitGenerator->get()
            );

        if (!$config) {
            $config = new UserColumnConfig();
            $config->setUser($this->getUser());
            $config->setRuestzeit($this->currentRuestzeitGenerator->get());
        }

        // Convert selected columns to configuration format
        $columns = [];
        foreach ($availableColumns as $index => $field) {
            $columns[] = [
                'field' => $field,
                'enabled' => in_array($field, $selectedColumns),
                'order' => $index
            ];
        }

        $config->setColumns($columns);
        $this->entityManager->persist($config);
        $this->entityManager->flush();

        // Redirect back to Anmeldungen list
        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(AnmeldungCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
    }
}
