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

namespace Inpsyde\Assets\Tests\Unit\Asset;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Handler\ScriptHandler;
use Inpsyde\Assets\OutputFilter\AsyncScriptOutputFilter;
use Inpsyde\Assets\OutputFilter\DeferScriptOutputFilter;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;
use org\bovigo\vfs\vfsStream;

class ScriptTest extends AbstractTestCase
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
        $script = new Script('foo', 'foo.js');

        static::assertTrue($script->inFooter());
        static::assertEmpty($script->localize());
        static::assertSame(ScriptHandler::class, $script->handler());
        static::assertSame(Asset::FRONTEND, $script->location());
    }

    /**
     * @test
     */
    public function testWithTranslation(): void
    {
        $script = new Script('handle', 'script.js');

        static::assertEmpty($script->translation());

        $expectedDomain = 'foo';
        $expectedPath = '/path/to/some/file.json';

        $script->withTranslation($expectedDomain, $expectedPath);

        static::assertSame(
            ['domain' => $expectedDomain, 'path' => $expectedPath],
            $script->translation()
        );
    }

    /**
     * @test
     * @dataProvider provideLocalized
     */
    public function testWithLocalize(string $objectName, $objectValue, $expected): void
    {
        $script = new Script('handle', 'script.js');

        static::assertEmpty($script->localize());

        $script->withLocalize($objectName, $objectValue);

        static::assertSame($expected, $script->localize());
    }

    /**
     * @test
     */
    public function testLocalizedSingleClosure(): void
    {
        $expected = ['foo' => ['bar' => 'baz']];
        $objectName = 'localizeObjectName';
        $script = new Script('handle', 'script.js', Asset::FRONTEND);
        $script->withLocalize(
            $objectName,
            static function () use ($expected): array {
                return $expected;
            }
        );

        static::assertSame([$objectName => $expected], $script->localize());
    }

    /**
     * @test
     */
    public function testInFooter(): void
    {
        $script = new Script('handle', 'script.js');

        // default is true
        static::assertTrue($script->inFooter());

        $script->isInHeader();
        static::assertFalse($script->inFooter());

        $script->isInFooter();
        static::assertTrue($script->inFooter());
    }

    /**
     * @test
     */
    public function testLocalizeCallable(): void
    {
        $expectedKey = 'foo';
        $expectedValue = ['bar' => 'baz'];
        $expected = [$expectedKey => $expectedValue];

        $script = new Script('handle', 'script.js', Asset::FRONTEND);
        $script->withLocalize(
            $expectedKey,
            static function () use ($expectedValue): array {
                return $expectedValue;
            }
        );

        static::assertSame($expected, $script->localize());
    }

    /**
     * @return void
     */
    public function testEnqueueCallable(): void
    {
        $expected = random_int(0, 100) > 50;

        $script = new Script('handle', 'script.js', Asset::FRONTEND);
        $script->canEnqueue(
            static function () use ($expected): bool {
                return $expected;
            }
        );

        static::assertSame($expected, $script->enqueue());
    }

    /**
     * @test
     */
    public function testInlineScripts(): void
    {
        $script = new Script('handle', 'foo.js');

        $expectedAppended = 'foo';
        $expectedPrepended = 'foo';

        static::assertEmpty($script->inlineScripts());

        $script->appendInlineScript($expectedAppended);
        $script->prependInlineScript($expectedPrepended);

        static::assertEquals(
            ['before' => [$expectedAppended], 'after' => [$expectedPrepended]],
            $script->inlineScripts()
        );
    }

    /**
     * @test
     * @deprecated
     */
    public function testUseAsyncFilter(): void
    {
        $script = new Script('handle', 'foo.js');
        static::assertEmpty($script->filters());

        $script->useAsyncFilter();
        static::assertSame(['async' => true], $script->attributes());
    }

    /**
     * @test
     * @deprecated
     */
    public function testUseDeferFilter(): void
    {
        $script = new Script('handle', 'foo.js');
        static::assertEmpty($script->filters());

        $script->useDeferFilter();
        static::assertSame(['defer' => true], $script->attributes());
    }

    /**
     * @return \Generator<string, array>
     */
    public function provideLocalized(): \Generator
    {
        yield 'string value' => [
            'objectName',
            'objectValue',
            ['objectName' => 'objectValue'],
        ];

        yield 'int value' => [
            'objectName',
            2,
            ['objectName' => 2],
        ];

        $expectedValue = ['foo', 'bar' => 'baz'];
        yield 'array value' => [
            'objectName',
            $expectedValue,
            ['objectName' => $expectedValue],
        ];

        yield 'closure' => [
            'objectName',
            static function (): string {
                return 'objectValue';
            },
            ['objectName' => 'objectValue'],
        ];
    }

    /**
     * @test
     * @dataProvider provideAssetsFile
     */
    public function testUseDependencyExtractionPlugin(
        string $fileName,
        string $fileContent,
        array $expectedDependencies,
        string $expectedVersion
    ): void {

        vfsStream::newFile($fileName)
            ->withContent($fileContent)
            ->at($this->root);

        $expectedFile = vfsStream::newFile('script.js')->at($this->root);

        $testee = new Script('script', $expectedFile->url());
        $testee->withFilePath($expectedFile->url());
        $testee->useDependencyExtractionPlugin();

        static::assertEqualsCanonicalizing(
            $expectedDependencies,
            $testee->dependencies()
        );

        static::assertSame($testee->version(), $expectedVersion);
    }

    /**
     * @return \Generator<string, array>
     */
    public function provideAssetsFile(): \Generator
    {
        $expectedDependencies = ['foo', 'bar', 'baz'];
        $expectedVersion = '1.0';

        yield 'json file' => [
            'script.asset.json',
            json_encode(['dependencies' => $expectedDependencies, 'version' => $expectedVersion]),
            $expectedDependencies,
            $expectedVersion,
        ];

        yield 'php file' => [
            'script.asset.php',
            // phpcs:disable
            '<?php return '
            . var_export(['dependencies' => $expectedDependencies, 'version' => $expectedVersion], true)
            . ';',
            // phpcs:enable
            $expectedDependencies,
            $expectedVersion,
        ];
    }

    /**
     * @test
     */
    public function testUseDependencyExtractionPluginUniqueDependencies(): void
    {
        $expectedDependencies = ['foo', 'bar', 'baz'];

        vfsStream::newFile('script.asset.json')
            ->withContent(json_encode(['dependencies' => $expectedDependencies]))
            ->at($this->root);

        $expectedFile = vfsStream::newFile('script.js')->at($this->root);

        $testee = new Script('script', $expectedFile->url());
        // Adding "foo" in first place should result in
        // just having "foo" once as dependency
        $testee->withDependencies('foo');
        $testee->withFilePath($expectedFile->url());
        $testee->useDependencyExtractionPlugin();

        static::assertEqualsCanonicalizing(
            $expectedDependencies,
            $testee->dependencies()
        );
    }

    /**
     * @test
     */
    public function testUseDependencyExtractionPluginWithDependencies(): void
    {
        $jsonDependencies = ['foo', 'bar', 'baz'];
        $registeredDependencies = ['bam'];

        $expectedDependencies = array_merge($jsonDependencies, $registeredDependencies);

        vfsStream::newFile('script.asset.json')
            ->withContent(json_encode(['dependencies' => $jsonDependencies]))
            ->at($this->root);

        $expectedFile = vfsStream::newFile('script.js')
            ->at($this->root);

        $testee = new Script('script', $expectedFile->url());
        $testee->useDependencyExtractionPlugin();
        $testee->withDependencies(...$registeredDependencies);
        $testee->withFilePath($expectedFile->url());

        static::assertEqualsCanonicalizing(
            $expectedDependencies,
            $testee->dependencies()
        );
    }

    /**
     * @test
     * @dataProvider provideVersions
     */
    public function testUseDependencyExtractionPluginWithVersion(
        ?string $withVersion,
        string $dependencyExtractionPluginVersion,
        string $expectedVersion
    ): void {

        vfsStream::newFile('script.asset.json')
            ->withContent(
                json_encode(
                    [
                        'dependencies' => [],
                        'version' => $dependencyExtractionPluginVersion,
                    ]
                )
            )
            ->at($this->root);

        $expectedFile = vfsStream::newFile('script.js')
            ->at($this->root);

        $testee = new Script('script', $expectedFile->url());
        $testee->useDependencyExtractionPlugin();
        if ($withVersion) {
            $testee->withVersion($withVersion);
        }
        $testee->withFilePath($expectedFile->url());

        static::assertSame($expectedVersion, $testee->version());
    }

    /**
     * @return \Generator<string, array>
     */
    public function provideVersions(): \Generator
    {
        yield 'version already set' => [
            '1.0',
            'foo',
            '1.0',
        ];

        yield 'version not set and resolved' => [
            null,
            '1.0',
            '1.0',
        ];
    }
}
