<?php

namespace App\Service\Product;

use App\Entity\Product;

final class ProductDownloadStorage
{
    public function __construct(
        private string $productDownloadDir
    ) {
    }

    public function getRootDir(): string
    {
        return rtrim($this->productDownloadDir, '/');
    }

    /**
     * @param array{key: string, label: string, path: string, filename: string, description?: string} $productFile
     */
    public function resolveReadablePath(array $productFile): ?string
    {
        $path = $this->normalizeRelativePath($productFile['path']);
        if ($path === null) {
            return null;
        }

        $resolvedPath = realpath($this->getRootDir() . '/' . $path);
        $resolvedRoot = realpath($this->getRootDir());
        if ($resolvedPath === false || $resolvedRoot === false) {
            return null;
        }

        if (!$this->isInsideRoot($resolvedPath, $resolvedRoot)) {
            return null;
        }

        if (!is_file($resolvedPath) || !is_readable($resolvedPath)) {
            return null;
        }

        return $resolvedPath;
    }

    /**
     * @return array<int,string>
     */
    public function findMissingFiles(Product $product): array
    {
        $missing = [];
        foreach ($product->getFiles() as $file) {
            if ($this->resolveReadablePath($file) === null) {
                $missing[] = $file['path'];
            }
        }

        return $missing;
    }

    private function normalizeRelativePath(string $path): ?string
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

    private function isInsideRoot(string $resolvedPath, string $resolvedRoot): bool
    {
        $resolvedRoot = rtrim($resolvedRoot, '/');

        return $resolvedPath === $resolvedRoot || str_starts_with($resolvedPath, $resolvedRoot . '/');
    }
}
