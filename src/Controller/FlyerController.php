<?php

namespace App\Controller;

use App\Service\FlyerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FlyerController extends AbstractController
{
    public function __construct(
        private FlyerService $flyerService
    ) {}

    #[Route('/flyer/{ruestzeitId}/download', name: 'flyer_download')]
    public function downloadFlyer(string $ruestzeitId): Response
    {
        return $this->flyerService->getFlyerResponse($ruestzeitId);
    }

    #[Route('/flyer/{ruestzeitId}/preview', name: 'flyer_preview')]
    public function previewFlyer(string $ruestzeitId): Response
    {
        return $this->flyerService->getFlyerPreviewResponse($ruestzeitId);
    }
}