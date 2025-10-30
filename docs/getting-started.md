---
nav_order: 1
---

# Getting started

## Getting started - the `AssetManager`

When using Assets in your theme or plugin, you can simply access the `Inpsyde\Assets\AssetManager` by hooking into the setup-hook.

This way you can start registering your assets:

```php
<?php
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\ScriptModule;
use Inpsyde\Assets\Style;

add_action(
	AssetManager::ACTION_SETUP,
	function(AssetManager $assetManager) {
		$assetManager->register(
			new Script('foo', 'www.example.com/script.js'),
			new ScriptModule('@my-plugin/module', 'www.example.com/module.js'),
			new Style('foo', 'www.example.com/style.css')
		);
	}
);
```

# Extending Assets

In some cases, when loading multiple Assets through a Loader like `Inpsyde\Assets\Loader\WebpackManifestLoader`, you will have the need to also extend one or multiple Assets loaded. 
Since the output of the `manifest.json` is fixed, we're limited with the Loader. In this case, we can make use of the `AssetManager::extendAsset()` method, which will allow us to add additional data to an Asset _before_ it is processed.

**manifest.json**
```json
{
    "script-handle": "/public/path/script.23dafsf2138d.js",
    "style-handle": "style.23dafsf2138d.css"
}
```

```php
<?php
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\ScriptModule;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Loader\WebpackManifestLoader;

add_action(
	AssetManager::ACTION_SETUP,
	function(AssetManager $assetManager) {
        $assetManager->extendAsset(
            'style-handle',
            Style::class,
            [
                'inline' => ['before' => ':root { --black: #000; }']
                'enqueue' => false,
            ]
        );
        $assetManager->extendAsset(
            'script-handle',
            Script::class,
            [
                'enqueue' => static fn(): bool => is_user_logged_in(),
                'inFooter' => true,
            ]
        );
	
	    $loader = new WebpackManifestLoader();
        /** @var \Inpsyde\Assets\Asset[] $assets */
        $assets = $loader->load('manifest.json');

		$assetManager->register(...$assets);
	}
);
```

> :information_source: **Via `array $extension`, you cannot change the `type` and `handle` and `url` of the Asset. Have a closer look into [Assets](./assets.md) documentation about the "Configuration API".**