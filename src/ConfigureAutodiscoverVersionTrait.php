<?php

declare(strict_types=1);

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @return self
     */
    public function enableAutodiscoverVersion(): self
    {
        $this->autodiscoverVersion = true;

        return $this;
    }

    /**
     * Disable automatic discovering of the version if no version is set.
     *
     * @return self
     */
    public function disableAutodiscoverVersion(): self
    {
        $this->autodiscoverVersion = false;

        return $this;
    }
}
