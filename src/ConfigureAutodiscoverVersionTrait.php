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
     * @var bool
     */
    protected $autodiscoverVersion = true;

    /**
     * Enable automatic discovering of the version if no version is set.
     *
     * @return static
     *
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function enableAutodiscoverVersion()
    {
        // phpcs:enable Inpsyde.CodeQuality.ReturnTypeDeclaration

        $this->autodiscoverVersion = true;

        return $this;
    }

    /**
     * Disable automatic discovering of the version if no version is set.
     *
     * @return static
     *
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function disableAutodiscoverVersion()
    {
        // phpcs:enable Inpsyde.CodeQuality.ReturnTypeDeclaration

        $this->autodiscoverVersion = false;

        return $this;
    }
}
