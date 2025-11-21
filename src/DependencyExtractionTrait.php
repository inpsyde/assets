<?php

declare(strict_types=1);

namespace Inpsyde\Assets;

trait DependencyExtractionTrait
{
    use ConfigureDependencyExtractionTrait;

    protected bool $resolvedDependencyExtractionPlugin = false;

    /**
     * @return bool
     *
     * phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
     * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
     * @psalm-suppress MixedArrayAccess
     * @psalm-suppress PossiblyFalseArgument
     * @psalm-suppress UnresolvableInclude
     */
    protected function resolveDependencyExtractionPlugin(): bool
    {
        if ($this->resolvedDependencyExtractionPlugin || !$this->dependencyExtraction) {
            return false;
        }
        $this->resolvedDependencyExtractionPlugin = true;

        $depsFile = $this->findDepdendencyFile();
        if (!$depsFile) {
            return false;
        }

        $depsFilePath = $depsFile->getPathname();
        $data = $depsFile->getExtension() === 'json'
            ? @json_decode((string) @file_get_contents($depsFilePath), true)
            : @require $depsFilePath;

        /** @var string[] $dependencies */
        $dependencies = $data['dependencies'] ?? [];
        /** @var string|null $version */
        $version = $data['version'] ?? null;

        $this->withDependencies(...$dependencies);
        if (!$this->version && $version) {
            $this->withVersion($version);
        }

        return true;
    }

    /**
     * Searching for in directory of the asset:
     *
     *      - {fileName}.asset.json
     *      - {fileName}.{hash}.asset.json
     *      - {fileName}.asset.php
     *      - {fileName}.{hash}.asset.php
     *
     * @return \DirectoryIterator|null
     */
    protected function findDepdendencyFile(): ?\DirectoryIterator
    {
        try {
            $filePath = $this->filePath();
            if ($filePath === '') {
                return null;
            }

            $path = dirname($filePath) . '/';

            $fileName = str_replace([$path, '.js'], '', $filePath);
            // It might be possible that the script file contains a version hash as well.
            // So we need to split it apart and just use the first part of the file.
            $fileNamePieces = explode('.', $fileName);
            $fileName = $fileNamePieces[0];

            $regex = '/' . $fileName . '(?:\.[a-zA-Z0-9]+)?\.asset\.(json|php)/';

            $depsFile = null;
            foreach (new \DirectoryIterator($path) as $fileInfo) {
                if (
                    $fileInfo->isDot()
                    || $fileInfo->isDir()
                    || !in_array($fileInfo->getExtension(), ['json', 'php'], true)
                ) {
                    continue;
                }
                if (preg_match($regex, $fileInfo->getFilename())) {
                    $depsFile = $fileInfo;
                    break;
                }
            }

            return $depsFile;
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
