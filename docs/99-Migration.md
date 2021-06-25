# Migration

## From 1.0 to 1.1
In version 1.1 we didn't implemented breaking changes, but we're renamend some internals for `Inpsyde\Assets\Asset` to be more clear when using the config-driven approach. In future you'll have to change your Asset-configuration, since we don't want to ship to much legacy code.

The config key `type` has been renamend to `location`, while `class` is now `type`. The `Inpsyde\Assets\Asset:type()` will be now `Inpsyde\Assets\Asset:location()`.

**Before:**
```php
<?php
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetFactory;
use Inpsyde\Assets\Style;

AssetFactory::create(
	[
		'handle' => 'my-handle',
		'src' => 'script.css',
		'type' => Asset::FRONTEND,
		'class' => Style::class,
	]
);
```

**After:**
```php
<?php
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetFactory;
use Inpsyde\Assets\Style;

AssetFactory::create(
	[
		'handle' => 'my-handle',
		'src' => 'script.css',
		'location' => Asset::FRONTEND,
		'type' => Style::class,
	]
);
```


## From 0.2 to 1.0
### Asset registration
In version 1.0 the function `Inpsyde\Assets\assetManager()` was replaced to improve the compatilibty with WordPress and to avoid calling `wp_styles()` and `wp_scripts()` too early. This is why the registration of an `Asset` now happens via hook callback instead of using the factory-function.

**Before:**
```php
<php
use function Inpsyde\Assets\assetManager;

assetManager()->register(...);
```

**After:**
```php
<?php
use Inpsyde\Assets\AssetManager;

add_action(
	AssetManager::ACTION_SETUP,
	function(AssetManager $assetManager)
	{
		$assetManager->register(...);
	}
);
```

### Renaming of `Asset`-flags and `AssetFactory::create` requirements
The type-flags from `Inpsyde\Assets\Asset` are renamed after the location where the given asset will be enqueued. Also the `type` is now no longer used to create instances of the class. Instead the class itself should be defined in `'class'`-field of the `$config` when using the `AssetFactory`.

This enables also the possiblity to use different implementations of `Inpsyde\Assets\Asset` with own `INpsyde\Assets\Handler\AssetHandler`.

**Before:**
```php
<?php
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetFactory;

AssetFactory::create(
	[
		'handle' => 'my-handle',
		'src' => 'script.css',
		'type' => Asset::TYPE_STYLE,
	]
);
```

**After:**
```php
<?php
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\AssetFactory;

AssetFactory::create(
	[
		'handle' => 'my-handle',
		'src' => 'script.css',
		'type' => Asset::FRONTEND,
		'class' => Style::class
	]
);
```

----

## From 0.1 to 0.2
In version 0.2 the function `Inpsyde\Assets\assetFactory()` was removed and replaced by the static factory.


**Before:**
```php
<?php
use function Inpsyde\Assets\assetFactory;

assetFactory()->create(...);
```

**After:**
```php
<?php
use Inpsyde\Assets\AssetFactory;

AssetFactory::create(...);
```
