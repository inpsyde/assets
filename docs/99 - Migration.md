# Migration

## From 0.2 to 1.0
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