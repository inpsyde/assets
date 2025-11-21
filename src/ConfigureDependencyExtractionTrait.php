<?php

declare(strict_types=1);

namespace Inpsyde\Assets;

trait ConfigureDependencyExtractionTrait
{
    /**
     * Set to "false" and the dependency extraction lookup will be disabled.
     *
     * @see self::disableDependencyExtraction()
     * @see self::enableDependencyExtraction()
     *
     */
    protected bool $dependencyExtraction = true;

    /**
     * Enable automatic lookup of dependency extraction files.
     *
     * @return static
     *
     * phpcs:disable Syde.Functions.ReturnTypeDeclaration.NoReturnType
     */
    public function enableDependencyExtraction()
    {
        // phpcs:enable Syde.Functions.ReturnTypeDeclaration.NoReturnType

        $this->dependencyExtraction = true;

        return $this;
    }

    /**
     * Disable automatic lookup of dependency extraction files.
     *
     * @return static
     *
     * phpcs:disable Syde.Functions.ReturnTypeDeclaration.NoReturnType
     */
    public function disableDependencyExtraction()
    {
        // phpcs:enable Syde.Functions.ReturnTypeDeclaration.NoReturnType

        $this->dependencyExtraction = false;

        return $this;
    }
}
