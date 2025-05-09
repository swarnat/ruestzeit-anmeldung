<?php

namespace App\Service;

use App\Entity\Ruestzeit;
use App\Repository\RuestzeitRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FlyerService
{
    public function __construct(
        private RuestzeitRepository $ruestzeitRepository,
        private UrlFileCache $urlFileCache
    ) {}

    /**
     * Find a Ruestzeit by its slug
     */
    public function findRuestzeitBySlug(string $ruestzeitId): ?Ruestzeit
    {
        return $this->ruestzeitRepository->findOneBy(["slug" => $ruestzeitId]);
    }

    /**
     * Create a response for a file from a URL
     */
    public function createFileResponse(string $url): Response
    {
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

    /**
     * Get a flyer response for download
     */
    public function getFlyerResponse(string $ruestzeitId): Response
    {
        $ruestzeit = $this->findRuestzeitBySlug($ruestzeitId);
        
        if (!$ruestzeit || !$ruestzeit->getFlyerUrl()) {
            throw new NotFoundHttpException('Flyer not found');
        }
        
        return $this->createFileResponse($ruestzeit->getFlyerUrl());
    }

    /**
     * Get a flyer preview response
     */
    public function getFlyerPreviewResponse(string $ruestzeitId): Response
    {
        $ruestzeit = $this->findRuestzeitBySlug($ruestzeitId);
        
        if (!$ruestzeit || !$ruestzeit->getImageUrl()) {
            throw new NotFoundHttpException('Preview image not found');
        }
        
        return $this->createFileResponse($ruestzeit->getImageUrl());
    }
}