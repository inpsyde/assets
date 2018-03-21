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

## Getting started
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

### AssetFactory
Sometimes it's easier to use a configuration file to manage your specific assets.

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

and later-on in your application:

```php
<?php
$assets = Inpsyde\Assets\assetFactory()->createFromFile('config/asset.php');

Inpsyde\Assets\assetManager()->registerMultiple($assets);
```

### Assets
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

### OutputFilter
These callbacks are specified to manipulate the output of the `Script` via `script_loader_tag` and `Style` via `style_loader_tag`.

Following default OutputFilters are shipped with this package:

### `AsyncScriptOutputFilter`

**Before:** `<script src="{url}"><script>`

**After:** `<script async src="{url}"><script>`

### `DeferScriptOutputFilter`
**Before:**  `<script src="{url}"><script>` 

**After:** `<script defer src="{url}"><script>`

### `AsyncStyleOutputFilter`
**Before:** `<link rel="stylesheet" href="{url}" />` 

**After:** 
```
<link rel="preload" href="{url}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="{url}" /></noscript>
<script>/* polyfill for older browsers */</script>
```

This OutputFilters delivers a polyfill for older browsers which is appended once to ensure that script-loading works properly.