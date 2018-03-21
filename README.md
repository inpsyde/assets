# Inpsyde Assets

## Introduction
Inpsyde Assets is a Composer package (not a plugin) that allows to deal with scripts and styles in a WordPress site.

## Installation

```
$ composer require inpsyde/assets
```


## Minimum Requirements and Dependencies

* PHP 7+
* WordPress latest-2

When installed for development, via Composer, Inpsyde Assets also requires:

* phpunit/phpunit (BSD-3-Clause)
* brain/monkey (MIT)
* inpsyde/php-coding-standards

## Getting started - the `AssetManager`
When using Assets in your theme or plugin, you can simply access `Inpsyde\Assets\assetManager()`, which returns an instance of `Inpsyde\Assets\AssetManager`.

This way you can start registering your assets:

```php
<?php
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

$myScript = new Script('foo', 'foo.js');
$myStyle = new Style('bar', 'bar.css');

Inpsyde\Assets\assetManager()
    ->register($myScript)
    ->register($myStyle);

// or
Inpsyde\Assets\assetManager()->registerMultiple(
    [
        $myScript,
        $myStyle,
    ]
);
```

## Using `AssetFactory`
Instead of creating instances by hand, it's sometimes easier to use configuration files to manage your specific assets.

**config/assets.php**
```php
<?php
use Inpsyde\Assets\Asset;

return [
    [
        'handle' => 'foo',
        'url' => 'example.com/assets/foo.css',
        'type' => Asset::TYPE_STYLE, 
    ],
    [
        'handle' => 'bar',
        'url' => 'example.com/assets/bar.js',
        'type' => Asset::TYPE_SCRIPT, 
    ],
];
``` 

In your application you can create all assets from that file by using the `Inpsyde\Assets\AssetFactory`:

```php
<?php
$assets = Inpsyde\Assets\assetFactory()->createFromFile('config/asset.php');

Inpsyde\Assets\assetManager()->registerMultiple($assets);
```

## Assets
There are two main classes delivered:

* `Inpsyde\Assets\Script` - dealing with JavaScript-files.
* `Inpsyde\Assets\Style` - dealing with CSS-files.

Each can receive a configuration injected into it's constructor. Following configurations are available:

|property|type|default|`Script`|`Style`|description|
|----|----|----|----|----|----|
|dependencies|array|`[]`|x|x|all defined depending handles|
|version|string|`''`|x|x|version of the given asset|
|enqueue|bool/callable|`true`|x|x|is the asset only registered or also enqueued|
|data|array/callable|`[]`|x|x|additional data assigned to the asset|
|filters|array|`[]`|x|x|an array of `Inpsyde\Assets\OutputFilter` or callable values to manipulate the output|
|localize|array|`[]`|x| |localized array of data attached to scripts|
|inFooter|bool|`true`|x| |defines if the current string is printed in footer|
|media|string|`'all'`| |x|type of media for the style|

## Using `OutputFilter`
These callbacks are specified to manipulate the output of the `Script` via `script_loader_tag` and `Style` via `style_loader_tag`.

To use an `OutputFilter` you've to assign them to a specific asset:

```php
<?php
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\OutputFilter\AsyncScriptOutputFilter;

$script = Inpsyde\Assets\assetFactory()->create(
	[
		'handle' => 'my-handle',
		'src' => 'script.js',
		'type' => Asset::TYPE_SCRIPT,
		'filters' => [ AsyncScriptOutputFilter::class ]
	]
);
```

### Available filters
Following default OutputFilters are shipped with this package:

#### `AsyncScriptOutputFilter`

This filter will add the `async`-attribute to your script-tag: `<script async src="{url}"><script>`

#### `DeferScriptOutputFilter`

This filter will add the `defer`-attribute to your script tag: `<script defer src="{url}"><script>`

#### `AsyncStyleOutputFilter`
This filter will allow you to load your CSS async via `preload`. It also delivers a polyfill for older browsers which is appended once to ensure that script-loading works properly.

```
<link rel="preload" href="{url}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="{url}" /></noscript>
<script>/* polyfill for older browsers */</script>
```

### Creating your own filter
You can either implement the `Inpsyde\Assets\OutputFilter\AssetOutputFilter`-interface or just use a normal callable function which will applied on the `Asset`:

```php
<?php
use Inpsyde\Assets\Asset;

$customFilter = function( string $html, Asset $asset ): string
{
    // do whatever you have to do.

    return $html;
};

$script = Inpsyde\Assets\assetFactory()->create(
	[
		'handle' => 'my-handle',
		'src' => 'script.js',
		'type' => Asset::TYPE_SCRIPT,
		'filters' => [ $customFilter ]
	]
);

```

## License and Copyright

Copyright (c) 2018 Inpsyde GmbH.

Inpsyde\Assets code is licensed under [MIT license](https://opensource.org/licenses/MIT).

The team at [Inpsyde](https://inpsyde.com) is engineering the Web since 2006.
