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

use Inpsyde\Assets\Handler\ExternalAssetHandler;

class ExternalAsset extends BaseAsset
{
    /** @noinspection MagicMethodsValidityInspection */
    public function __construct(string $handle)
    {
        $this->handle = $handle;
    }

    /**
     * @return string
     */
    protected function defaultHandler(): string
    {
        return ExternalAssetHandler::class;
    }
}
