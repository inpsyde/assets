# `AssetFactory`
Instead of creating instances by hand, it's sometimes easier to use configuration via array or file to manage your specific assets.

## `AssetFactory::create()`

Creating a single Asset from a configuration Array you can do following:

```php
<?php
use Inpsyde\Assets\AssetFactory;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Style;

$asset = AssetFactory::create(
    [
    		'handle' => 'foo',
    		'url' => 'example.com/assets/foo.css',
    		'location' => Asset::FRONTEND,
    		'type' => Style::class
        ],
);
```

## `AssetFactory::createFromArray()`

To create multiple Assets you can use following:

```php
<?php
use Inpsyde\Assets\AssetFactory;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

$assets = AssetFactory::createFromArray(
    [
        [
            'handle' => 'foo',
            'url' => 'example.com/assets/foo.css',
            'location' => Asset::FRONTEND,
            'type' => Style::class
        ],
        [
            'handle' => 'bar',
            'url' => 'example.com/assets/bar.js',
            'location' => Asset::FRONTEND,
            'type' => Script::class
        ],
    ]
);
```

## `AssetFactory::createFromFile()`

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
		'url' => 'example.com/assets/foo.css',
		'location' => Asset::FRONTEND,
		'type' => Style::class
    ],
    [
		'handle' => 'bar',
		'url' => 'example.com/assets/bar.js',
		'location' => Asset::FRONTEND,
		'type' => Script::class
    ],
];
``` 

And in your application:

```php
<?php
use Inpsyde\Assets\AssetFactory;

$assets = AssetFactory::createFromFile('config/assets.php');
```
