---
title: "Asset Factory"
nav_order: 2
layout: "default"
---
# Asset Factory
{: .fw-500 }

## `AssetFactory`
Instead of creating instances by hand, it's sometimes easier to use configuration via array or file to manage your specific assets.

**[!] Note:** The `AssetFactory` is currently replaced step by step via Loaders. Methods are set to `@deprecated` which have been moved to a Loader.

## `AssetFactory::create()`

To create a single Asset from an array, you can do following:

```php
<?php
use Inpsyde\Assets\AssetFactory;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Style;

/** @var Style $asset */
$asset = AssetFactory::create(
    [
    		'handle' => 'foo',
    		'url' => 'www.example.com/assets/style.css',
    		'location' => Asset::FRONTEND,
    		'type' => Style::class
        ],
);
```