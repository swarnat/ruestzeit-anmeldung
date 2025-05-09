<?php

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UrlFileCache
{
    private string $cacheDir;
    private Filesystem $filesystem;
    private HttpClientInterface $httpClient;
    private MimeTypes $mimeTypes;

    public function __construct(
        KernelInterface $kernel,
        HttpClientInterface $httpClient
    ) {
        $this->cacheDir = $kernel->getCacheDir() . '/url_cache';
        $this->filesystem = new Filesystem();
        $this->httpClient = $httpClient;
        $this->mimeTypes = new MimeTypes();
        
        // Ensure cache directory exists
        if (!$this->filesystem->exists($this->cacheDir)) {
            $this->filesystem->mkdir($this->cacheDir);
        }
    }

    /**
     * Downloads a file from a URL and caches it locally
     * 
     * @param string $url The URL of the file
     * @return array An array containing the file path and content type
     */
    public function downloadAndCache(string $url): array
    {
        // Create a hash of the URL to use as the filename
        $filename = hash("sha256", $url);
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        if ($extension) {
            $filename .= '.' . $extension;
        }
        
        $cachePath = $this->cacheDir . '/' . $filename;
        $metaPath = $this->cacheDir . '/' . $filename . '.meta';
        
        // Check if file already exists in cache and is less than 24 hours old
        if ($this->filesystem->exists($cachePath) && 
            $this->filesystem->exists($metaPath) &&
            (time() - filemtime($cachePath) < 86400)) {
            $metadata = json_decode(file_get_contents($metaPath), true);
            return [
                'path' => $cachePath,
                'contentType' => $metadata['ContentType'] ?? $this->determineContentType($cachePath, $extension)
            ];
        }
        
        // Download the file
        $response = $this->httpClient->request('GET', $url);
        $content = $response->getContent();
        
        // Save file content to cache
        file_put_contents($cachePath, $content);
        
        // Determine content type
        $contentType = $response->getHeaders()['content-type'][0] ?? null;
        
        // If content type is not provided in the response, try to determine it from the file
        if (!$contentType) {
            $contentType = $this->determineContentType($cachePath, $extension);
        }
        
        // Save metadata to cache
        $metadata = [
            'ContentType' => $contentType,
            'LastModified' => time(),
            'Url' => $url
        ];
        file_put_contents($metaPath, json_encode($metadata));
        
        return [
            'path' => $cachePath,
            'contentType' => $contentType
        ];
    }
    
    /**
     * Determines the content type of a file
     * 
     * @param string $filePath The path to the file
     * @param string|null $extension The file extension
     * @return string The content type
     */
    private function determineContentType(string $filePath, ?string $extension = null): string
    {
        // Try to determine content type from file content
        $contentType = mime_content_type($filePath);
        
        // If that fails, try to determine from extension
        if (!$contentType || $contentType === 'application/octet-stream') {
            if ($extension) {
                $mimeTypes = $this->mimeTypes->getMimeTypes($extension);
                if (!empty($mimeTypes)) {
                    $contentType = $mimeTypes[0];
                }
            }
        }
        
        // Default to octet-stream if all else fails
        return $contentType ?: 'application/octet-stream';
    }
}