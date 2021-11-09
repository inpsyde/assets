---
nav_order: 2
---
# Asset Factory
{: .no_toc }
## Table of contents
{: .no_toc .text-delta }
1. TOC
{:toc}
---

## `AssetFactory`
Instead of creating instances by hand, it's sometimes easier to use configuration via array or file to manage your specific assets.

**:warning: Note:** The `AssetFactory` is replaced step by step via Loaders. Methods are set to `@deprecated` which have been moved to a Loader.

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
