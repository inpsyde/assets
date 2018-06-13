# Assets
There are two main classes delivered:

* `Inpsyde\Assets\Script` - dealing with JavaScript-files.
* `Inpsyde\Assets\Style` - dealing with CSS-files.

Each instance requires a `string $handle`, `string $url`, `int $location` and optionally a configuration via `array $config`. 

Following configurations are available:

|property|type|default|`Script`|`Style`|description|
|----|----|----|----|----|----|
|dependencies|array|`[]`|x|x|all defined depending handles|
|location|int|falls back to `Asset::FRONTEND`|x|x|depending on location of the `Asset`, it will be enqueued with different hooks|
|version|string|`''`|x|x|version of the given asset|
|enqueue|bool/callable|`true`|x|x|is the asset only registered or also enqueued|
|data|array/callable|`[]`|x|x|additional data assigned to the asset|
|filters|callable[]|`[]`|x|x|an array of `Inpsyde\Assets\OutputFilter` or callable values to manipulate the output|
|handler|string|`ScriptHandler::class` or `StyleHandler::class`|x|x|The handler which will be used to register/enqueue the Asset|
|localize|array/callable|`[]`|x| |localized array of data attached to `Script`|
|inFooter|bool|`true`|x| |defines if the current `Script` is printed in footer|
|media|string|`'all'`| |x|type of media for the `Style`|


## Asset locations
By default the package comes with predefined locations of assets:

|const|hook|location|
|---|---|---|
|`Asset::FRONTEND`|`wp_enqueue_scripts`|Frontend|
|`Asset::BACKEND`|`admin_enqueue_scripts`|Backend| 
|`Asset::LOGIN`|`login_enqueue_scripts`|wp-login.php|
|`Asset::CUSTOMIZER`|`customize_controls_enqueue_scripts`|Customizer|
|`Asset::BLOCK_EDITOR_ASSETS`|`enqueue_block_editor_assets`|Gutenberg Editor|
|`Asset::BLOCK_ASSETS`|`enqueue_block_assets`|Gutenberg Editor and Frontend|

## Using multiple locations
To avoid duplicated registration of Assets in different locations such as backend and frontend, it is possible to add multiple ones via bitwise operator `|` (OR).

Here's a short example for a `Style` which will be enqueued in frontend *and* backend:

```php
<?php
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Asset;

add_action( 
	AssetManager::ACTION_SETUP, 
	function(AssetManager $assetManager) {
	
		$assetManager->register(
			new Style('foo', 'foo.css', Asset::BACKEND | Asset::FRONTEND )
		);
	}
);
```
