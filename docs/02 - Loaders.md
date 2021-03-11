# Loaders

## WebpackLoaders
Webpack is a module bundler. Its main purpose is to bundle JavaScript files for usage in a browser, yet it is also capable of transforming, bundling, or packaging just about any resource or asset.

There are 2 different Loaders available which are using generated `json`-files from Webpack:

- `WebpackManifestLoader`
- `EncoreEntrypointsLoader`

### `WebpackManifestLoader`
The [webpack-manifest-plugin](https://www.npmjs.com/package/webpack-manifest-plugin) creates a `manifest.json`-file in your root output directory with a mapping of all source file names to their corresponding output file, for example:

**manifest.json**
```json
{
    "script.js": "/public/path/script.23dafsf2138d.js",
    "style.css": "style.23dafsf2138d.css",
    "sub-folder/style.css": ""
}
```

To load this file in your application you can do following:

```php
<?php
use Inpsyde\Assets\Loader\WebpackManifestLoader;

$loader = new WebpackManifestLoader();
/** @var \Inpsyde\Assets\Asset[] $assets */
$assets = $loader->load('manifest.json');
```

If the Asset URL needs to be changed, you can use following:

```php
<?php
use Inpsyde\Assets\Loader\WebpackManifestLoader;

$loader = new WebpackManifestLoader();
$loader->withDirectoryUrl('www.example.com/path/to/assets/');
/** @var \Inpsyde\Assets\Asset[] $assets */
$assets = $loader->load('manifest.json');
```

### `EncoreEntrypointsLoader`

[Symfony Webpack Encore](https://symfony.com/doc/current/frontend.html) provides a custom implementation of the [assets-webpack-plugin](https://www.npmjs.com/package/assets-webpack-plugin) which groups asset chunks into a single array for a given handle.

The `EncoreEntrypointsLoader` can load those configurations and automatically configure your dependencies for splitted chunks.

**entrypoints.json**

```json
{
    "entrypoints": {
         "theme": {
             "css": [
                 "./theme.css",
                 "./theme1.css",
                 "./theme2.css",
             ]
        }
     }
}
``` 

And loading this file:

```php
<?php
use Inpsyde\Assets\Loader\EncoreEntrypointsLoader;

$loader = new EncoreEntrypointsLoader();
/** @var \Inpsyde\Assets\Asset[] $assets */
$assets = $loader->load('entrypoints.json');

$second = $assets[1]; // theme1.css
$second->dependencies(); // handle from $asset[0]

$third = $assets[2]; // theme2.css
$third->dependencies(); // handles from $asset[1] and $asset[2]
```


## `ArrayLoader`

To create multiple Assets you can use following:

```php
<?php
use Inpsyde\Assets\Loader\ArrayLoader;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

$config = [
    [
        'handle' => 'foo',
        'url' => 'www.example.com/assets/style.css',
        'location' => Asset::FRONTEND,
        'type' => Style::class
    ],
    [
        'handle' => 'bar',
        'url' => 'www.example.com/assets/bar.js',
        'location' => Asset::FRONTEND,
        'type' => Script::class
    ],
];

$loader = new ArrayLoader();
/** @var Asset[] $assets */
$assets = $loader->load($config);
```

## `PhpFileLoader`

If you want to avoid having large array configuration in your code, you can move everything to an external PHP-file which returns the array:

**config/assets.php**
```php
<?php
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

return [
    [
		'handle' => 'foo',
		'url' => 'www.example.com/assets/style.css',
		'location' => Asset::FRONTEND,
		'type' => Style::class
    ],
    [
		'handle' => 'bar',
		'url' => 'www.example.com/assets/bar.js',
		'location' => Asset::FRONTEND,
		'type' => Script::class
    ],
];
``` 

And in your application:

```php
<?php
use Inpsyde\Assets\Loader\PhpFileLoader;
use Inpsyde\Assets\Asset;

$loader = new PhpFileLoader();
/** @var Asset[] $assets */
$assets = $loader->load('config/assets.php');
```


## Configure autodiscovering version

In webpack it is possible to configure file name versioning which will produce something like: `script.{hash}.js`. To support versioning via file name you can simply disable the auto discovering of file versioning like this:

```php
<?php
use Inpsyde\Assets\Loader\WebpackManifestLoader;

$loader = new WebpackManifestLoader();
$loader->disableAutodiscoverVersion();
/** @var \Inpsyde\Assets\Asset[] $assets */
$assets = $loader->load('manifest.json');
```

**[!]** All 4 loaders supporting to disable the auto discovering of version.