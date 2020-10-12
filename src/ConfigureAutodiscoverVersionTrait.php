<?php

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
