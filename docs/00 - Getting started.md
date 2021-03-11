# Getting started - the `AssetManager`
When using Assets in your theme or plugin, you can simply access the `Inpsyde\Assets\AssetManager` by hooking into the setup-hook.

This way you can start registering your assets:

```php
<?php
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Asset;


add_action( 
	AssetManager::ACTION_SETUP, 
	function(AssetManager $assetManager) {
	
		$assetManager->register(
			new Script('foo', 'www.example.com/script.js'),
			new Style('foo', 'www.example.com/style.css')
		);
	}
);
```