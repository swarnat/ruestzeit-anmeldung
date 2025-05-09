<?php

namespace App\Controller;

use App\Entity\Ruestzeit;
use App\Repository\RuestzeitRepository;
use App\Service\UrlFileCache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class FlyerController extends AbstractController
{
    public function __construct(
        private RuestzeitRepository $ruestzeitRepository,
        private UrlFileCache $urlFileCache
    ) {}

    #[Route('/flyer/{ruestzeitId}/download', name: 'flyer_download')]
    public function downloadFlyer(string $ruestzeitId): Response
    {
        // Find the Ruestzeit entity
        $ruestzeit = $this->ruestzeitRepository->findOneBy(["slug" => $ruestzeitId ]);
        
        if (!$ruestzeit || !$ruestzeit->getFlyerUrl()) {
            throw new NotFoundHttpException('Flyer not found');
        }
        
        $url = $ruestzeit->getFlyerUrl();

        // Download and cache the file from the URL
        $result = $this->urlFileCache->downloadAndCache($url);
        
        // Create a BinaryFileResponse with the cached file
        $response = new BinaryFileResponse($result['path']);
        
        // Set the content type
        $response->headers->set('Content-Type', $result['contentType']);
        
        // Set the content disposition to attachment with the original filename
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            basename($url)
        );
        $response->headers->set('Content-Disposition', $disposition);
        
        return $response;
    }

    #[Route('/flyer/{ruestzeitId}/preview', name: 'flyer_preview')]
    public function previewFlyer(string $ruestzeitId): Response
    {
        // Find the Ruestzeit entity
        $ruestzeit = $this->ruestzeitRepository->findOneBy(["slug" => $ruestzeitId ]);
        
        if (!$ruestzeit || !$ruestzeit->getFlyerUrl()) {
            throw new NotFoundHttpException('Flyer not found');
        }
        
        $url = $ruestzeit->getImageUrl();

        // Download and cache the file from the URL
        $result = $this->urlFileCache->downloadAndCache($url);
        
        // Create a BinaryFileResponse with the cached file
        $response = new BinaryFileResponse($result['path']);
        
        // Set the content type
        $response->headers->set('Content-Type', $result['contentType']);
        
        // Set the content disposition to attachment with the original filename
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            basename($url)
        );
        $response->headers->set('Content-Disposition', $disposition);
        
        return $response;
    }
}