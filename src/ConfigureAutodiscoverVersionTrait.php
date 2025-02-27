<?php

declare(strict_types=1);

namespace Inpsyde\Assets;

trait ConfigureAutodiscoverVersionTrait
{
    /**
     * Set to "false" and the version will not automatically discovered.
     *
     * @see self::disableAutodiscoverVersion()
     * @see self::enableAutodiscoverVersion()
     *
     */
    protected bool $autodiscoverVersion = true;

    /**
     * Enable automatic discovering of the version if no version is set.
     *
     * @return static
     *
     * phpcs:disable Syde.Functions.ReturnTypeDeclaration.NoReturnType
     */
    public function enableAutodiscoverVersion()
    {
        // phpcs:enable Syde.Functions.ReturnTypeDeclaration.NoReturnType

        $this->autodiscoverVersion = true;

        return $this;
    }

    /**
     * Disable automatic discovering of the version if no version is set.
     *
     * @return static
     *
     * phpcs:disable Syde.Functions.ReturnTypeDeclaration.NoReturnType
     */
    public function disableAutodiscoverVersion()
    {
        // phpcs:enable Syde.Functions.ReturnTypeDeclaration.NoReturnType

        $this->autodiscoverVersion = false;

        return $this;
    }
}
