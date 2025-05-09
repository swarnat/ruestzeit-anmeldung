<?php

namespace App\Service;

use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Filesystem;

class S3FileUploader
{
    private S3Client $s3Client;
    private string $bucketName;
    private SluggerInterface $slugger;
    private ?string $customUrl;
    private string $cacheDir;
    private Filesystem $filesystem;

    public function __construct(
        S3Client $s3Client, 
        string $bucketName, 
        SluggerInterface $slugger, 
        KernelInterface $kernel
    ) {
        $this->s3Client = $s3Client;
        $this->bucketName = $bucketName;
        $this->slugger = $slugger;
        $this->customUrl = getenv('AWS_CUSTOM_URL') ?? null;
        $this->cacheDir = $kernel->getCacheDir() . '/s3_cache';
        $this->filesystem = new Filesystem();
        
        // Ensure cache directory exists
        if (!$this->filesystem->exists($this->cacheDir)) {
            $this->filesystem->mkdir($this->cacheDir);
        }
    }

    public function upload(UploadedFile $file, string $directory): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
        $key = trim($directory, '/') . '/' . $newFilename;

        $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'Key' => $key,
            'Body' => fopen($file->getPathname(), 'rb'),
            'ACL' => 'public-read',
            'ContentType' => $file->getMimeType()
        ]);

        return $key;
    }

    public function getPublicUrl(string $key): string
    {
        if ($this->customUrl) {
            return str_replace('{key}', $key, rtrim($this->customUrl, '/'));
        }

        $endpoint = $this->s3Client->getEndpoint();
        if ($endpoint) {
            return sprintf(
                '%s/%s/%s',
                rtrim($endpoint->__toString(), '/'),
                $this->bucketName,
                $key
            );
        }

        // Fallback to standard S3 URL if no custom endpoint
        return sprintf(
            'https://%s.s3.%s.amazonaws.com/%s',
            $this->bucketName,
            $this->s3Client->getRegion(),
            $key
        );
    }
    
    /**
     * Downloads a file from S3 and caches it locally along with its content type
     *
     * @param string $key The S3 key of the file
     * @return string The path to the cached file
     */
    public function downloadAndCache(string $key): string
    {
        // Create a hash of the key to use as the filename
        $filename = hash("sha256", $key);
        $extension = pathinfo($key, PATHINFO_EXTENSION);
        if ($extension) {
            $filename .= '.' . $extension;
        }

        $cachePath = $this->cacheDir . '/' . $filename;
        $metaPath = $this->cacheDir . '/' . $filename . '.meta';
        
        // Check if file already exists in cache and is less than 24 hours old
        if ($this->filesystem->exists($cachePath) &&
            $this->filesystem->exists($metaPath) &&
            (time() - filemtime($cachePath) < 86400)) {
            return $cachePath;
        }
        
        // Download the file from S3
        $result = $this->s3Client->getObject([
            'Bucket' => $this->bucketName,
            'Key' => $key
        ]);
        
        // Save file content to cache
        file_put_contents($cachePath, $result['Body']);
        
        // Save metadata (content type) to cache
        $metadata = [
            'ContentType' => $result['ContentType'] ?? 'application/octet-stream',
            'LastModified' => time(),
            'Key' => $key
        ];
        file_put_contents($metaPath, json_encode($metadata));
        
        return $cachePath;
    }
    
    /**
     * Gets the content type of a file from S3 or cache
     *
     * @param string $key The S3 key of the file
     * @return string The content type or application/octet-stream if not found
     */
    public function getContentType(string $key): string
    {
        // Create the same hash as in downloadAndCache
        $filename = hash("sha256", $key);
        $extension = pathinfo($key, PATHINFO_EXTENSION);
        if ($extension) {
            $filename .= '.' . $extension;
        }
        
        $metaPath = $this->cacheDir . '/' . $filename . '.meta';
        
        // Check if metadata exists in cache and is less than 24 hours old
        if ($this->filesystem->exists($metaPath) &&
            (time() - filemtime($metaPath) < 86400)) {
            $metadata = json_decode(file_get_contents($metaPath), true);
            if (isset($metadata['ContentType'])) {
                return $metadata['ContentType'];
            }
        }
        
        // If not in cache or cache is expired, get from S3
        try {
            $result = $this->s3Client->headObject([
                'Bucket' => $this->bucketName,
                'Key' => $key
            ]);
            
            $contentType = $result['ContentType'] ?? 'application/octet-stream';
            
            // Cache the content type
            $metadata = [
                'ContentType' => $contentType,
                'LastModified' => time(),
                'Key' => $key
            ];
            
            // Ensure directory exists
            if (!$this->filesystem->exists($this->cacheDir)) {
                $this->filesystem->mkdir($this->cacheDir);
            }
            
            file_put_contents($metaPath, json_encode($metadata));
            
            return $contentType;
        } catch (\Exception $e) {
            return 'application/octet-stream';
        }
    }
}
