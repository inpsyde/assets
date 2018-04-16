# Getting started - the `AssetManager`
When using Assets in your theme or plugin, you can simply access the `Inpsyde\Assets\AssetManager` by hooking into the setup-hook.

This way you can start registering your assets:

```php
<?php
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Asset;

// Frontend Assets
add_action( 
	AssetManager::ACTION_SETUP, 
	function(AssetManager $assetManager) {
	
		$assetManager->register(
			new Script('foo', 'foo.js'),
			new Style('foo', 'foo.css')
		);
	}
);

// Backend Assets
add_action( 
	AssetManager::ACTION_SETUP, 
	function(AssetManager $assetManager) {
	
		$assetManager->register(
			new Script('foo-admin', 'foo-admin.js', Asset::TYPE_ADMIN_SCRIPT),
			new Style('foo-admin', 'foo-admin.css', Asset::TYPE_ADMIN_STYLE)
		);
	}
);
```