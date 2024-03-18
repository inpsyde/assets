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

namespace Inpsyde\Assets\Tests\Unit\OutputFilter;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Inpsyde\Assets\OutputFilter\AttributesOutputFilter;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class AttributesOutputFilterTest extends AbstractTestCase
{
    /**
     * phpcs:disable WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
     * phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting
     * phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
     */
    public function testIfTagProcessorIsUnavailable(): void
    {
        $currentErrorReporting = error_reporting();
        error_reporting($currentErrorReporting | \E_USER_DEPRECATED);
        $errorMessages = [];

        set_error_handler(
            static function (int $code, string $msg) use (&$errorMessages): bool {
                $errorMessages[] = $msg;
                return true;
            },
            \E_USER_DEPRECATED
        );

        $testee = new AttributesOutputFilter();

        $stub = \Mockery::mock(Asset::class);
        $stub->expects('attributes')->andReturn([
            'key' => 'value',
        ]);

        $input = '<script src="foo.js"></script>';

        static::assertInstanceOf(AssetOutputFilter::class, $testee);
        static::assertSame($input, $testee($input, $stub));
        static::assertSame(
            'Adding attributes is not supported for WordPress < 6.2',
            $errorMessages[0]
        );

        error_reporting($currentErrorReporting);
        // phpcs:enable
    }
}
