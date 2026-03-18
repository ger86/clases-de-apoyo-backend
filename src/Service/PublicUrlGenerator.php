<?php

namespace App\Service;

use App\Entity\SonataMediaMedia;
use Aws\S3\S3Client;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PublicUrlGenerator
{

    public function __construct(
        private S3Client $s3Client,
        private Pool $mediaService,
        #[Autowire(env: 'S3_BUCKET_NAME')]
        private string $bucketName,
        #[Autowire('%app.stripe.secret_key%')]
        private string $providerName
    ) {
    }

    public function generate(SonataMediaMedia $media): string
    {
        $provider = $this->mediaService->getProvider($media->getProviderName());
        $path = $provider->generatePrivateUrl($media, 'reference');
        $command = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $this->bucketName,
            'Key' => $path
        ]);
        $request = $this->s3Client->createPresignedRequest($command, '+30 minutes');
        $uri = (string) $request->getUri();
        return $uri;
    }
}
