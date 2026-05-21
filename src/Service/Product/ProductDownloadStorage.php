<?php

namespace App\Service\Product;

use App\Entity\Product;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ProductDownloadStorage
{
    public function __construct(
        private S3Client $s3Client,
        #[Autowire(env: 'S3_BUCKET_NAME')]
        private string $bucketName,
        #[Autowire('%app.product_download_presigned_ttl%')]
        private string $presignedTtl
    ) {
    }

    public function getStorageDescription(): string
    {
        return \sprintf('s3://%s', $this->bucketName);
    }

    /**
     * @param array{key: string, label: string, path: string, filename: string, description?: string} $productFile
     */
    public function createDownloadUrl(array $productFile): ?string
    {
        $objectKey = $this->normalizeObjectKey($productFile['path']);
        if ($objectKey === null || !$this->objectExists($objectKey)) {
            return null;
        }

        $command = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $this->bucketName,
            'Key' => $objectKey,
            'ResponseContentDisposition' => \sprintf('attachment; filename="%s"', addslashes($productFile['filename'])),
            'ResponseContentType' => 'application/pdf',
        ]);

        return (string) $this->s3Client->createPresignedRequest($command, $this->presignedTtl)->getUri();
    }

    /**
     * @return array<int,string>
     */
    public function findMissingFiles(Product $product): array
    {
        $missing = [];
        foreach ($product->getFiles() as $file) {
            $objectKey = $this->normalizeObjectKey($file['path']);
            if ($objectKey === null || !$this->objectExists($objectKey)) {
                $missing[] = $file['path'];
            }
        }

        return $missing;
    }

    private function normalizeObjectKey(string $path): ?string
    {
        $path = ltrim($path, '/');
        $legacyPrefix = 'var/product-downloads/';
        if (str_starts_with($path, $legacyPrefix)) {
            $path = substr($path, \strlen($legacyPrefix));
        }

        if ($path === '' || str_contains($path, "\0")) {
            return null;
        }

        foreach (explode('/', $path) as $segment) {
            if ($segment === '..') {
                return null;
            }
        }

        return $path;
    }

    private function objectExists(string $objectKey): bool
    {
        try {
            $this->s3Client->headObject([
                'Bucket' => $this->bucketName,
                'Key' => $objectKey,
            ]);

            return true;
        } catch (AwsException $exception) {
            if (\in_array($exception->getStatusCode(), [403, 404], true)) {
                return false;
            }

            throw $exception;
        }
    }
}
