<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Tests\Unit\Asset;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Handler\ScriptModuleHandler;
use Inpsyde\Assets\ScriptModule;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;
use org\bovigo\vfs\vfsStream;

class ScriptModuleTest extends AbstractTestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup('tmp');
        parent::setUp();
    }

    /**
     * @test
     */
    public function testBasic(): void
    {
        $scriptModule = new ScriptModule('foo', 'foo.js');

        static::assertSame(ScriptModuleHandler::class, $scriptModule->handler());
        static::assertSame(Asset::FRONTEND | Asset::ACTIVATE, $scriptModule->location());
    }

    /**
     * @test
     */
    public function testWithData(): void
    {
        $scriptModule = new ScriptModule('handle', 'script.js');

        static::assertEmpty($scriptModule->data());

        $expectedData = ['foo' => 'bar', 'baz' => 'qux'];
        $scriptModule->withData($expectedData);

        static::assertSame($expectedData, $scriptModule->data());
    }

    /**
     * @test
     */
    public function testDependencyExtractionCanBeDisabled(): void
    {
        $expectedDependencies = ['foo', 'bar', 'baz'];
        $expectedVersion = '1.0';

        vfsStream::newFile('script.asset.json')
            ->withContent(
                json_encode(
                    [
                        'dependencies' => $expectedDependencies,
                        'version' => $expectedVersion,
                    ]
                )
            )
            ->at($this->root);

        $expectedFile = vfsStream::newFile('script.js')->at($this->root);

        $testee = new ScriptModule('script', $expectedFile->url(), Asset::FRONTEND, false);
        $testee->withFilePath($expectedFile->url());

        // Dependencies should not be loaded from the .asset.json file
        static::assertEmpty($testee->dependencies());

        // Version is still autodiscovered from file modification time, not from .asset.json
        // To verify it's not from .asset.json, we check it's not the expected version
        static::assertNotEquals($expectedVersion, $testee->version());
    }

    /**
     * @test
     */
    public function testDependencyExtractionEnabledByDefault(): void
    {
        $expectedDependencies = ['foo', 'bar', 'baz'];

        vfsStream::newFile('script.asset.json')
            ->withContent(json_encode(['dependencies' => $expectedDependencies]))
            ->at($this->root);

        $expectedFile = vfsStream::newFile('script.js')->at($this->root);

        $testee = new ScriptModule('script', $expectedFile->url());
        $testee->withFilePath($expectedFile->url());

        // Should load dependencies by default
        static::assertEqualsCanonicalizing(
            $expectedDependencies,
            $testee->dependencies()
        );
    }
}
