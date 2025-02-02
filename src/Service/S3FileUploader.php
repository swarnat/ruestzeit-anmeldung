<?php

namespace App\Service;

use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class S3FileUploader
{
    private S3Client $s3Client;
    private string $bucketName;
    private SluggerInterface $slugger;
    private ?string $customUrl;

    public function __construct(S3Client $s3Client, string $bucketName, SluggerInterface $slugger)
    {
        $this->s3Client = $s3Client;
        $this->bucketName = $bucketName;
        $this->slugger = $slugger;
        $this->customUrl = getenv('AWS_CUSTOM_URL') ?? null;
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
}
