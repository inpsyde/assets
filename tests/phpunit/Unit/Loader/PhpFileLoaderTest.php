<?php declare(strict_types=1); # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit\Loader;

use Inpsyde\Assets\Loader\PhpFileLoader;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class PhpFileLoaderTest extends AbstractTestCase
{

    /**
     * @var  vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $this->root = vfsStream::setup('tmp');
        parent::setUp();
    }

    /**
     * @expectedException \Inpsyde\Assets\Exception\FileNotFoundException
     */
    public function testLoadFileNotFound()
    {
        (new PhpFileLoader())->load('foo');
    }

    public function testLoad()
    {
        $content = <<<FILE
<?php 
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

return [
    [
        'handle' => 'foo',
        'url' => 'foo.css',
        'location' => Asset::FRONTEND,
        'type' => Style::class,
    ],
    [
        'handle' => 'bar',
        'url' => 'bar.js',
        'location' => Asset::FRONTEND,
        'type' => Script::class,
    ],
];
FILE;

        $filePath = vfsStream::newFile('config.php')
            ->withContent($content)
            ->at($this->root)
            ->url();

        $testee = new PhpFileLoader();
        $assets = $testee->load($filePath);
        static::assertCount(2, $assets);
        static::assertInstanceOf(Style::class, $assets[0]);
        static::assertInstanceOf(Script::class, $assets[1]);
    }
}