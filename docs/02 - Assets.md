# Assets
There are two main classes delivered:

* `Inpsyde\Assets\Script` - dealing with JavaScript-files.
* `Inpsyde\Assets\Style` - dealing with CSS-files.

Each can receive a configuration injected into it's constructor. Following configurations are available:

|property|type|default|`Script`|`Style`|description|
|----|----|----|----|----|----|
|dependencies|array|`[]`|x|x|all defined depending handles|
|type|int|falls back to `Asset::FRONTEND`|x|x|depending on type the `Asset` will enqueued in different locations|
|version|string|`''`|x|x|version of the given asset|
|enqueue|bool/callable|`true`|x|x|is the asset only registered or also enqueued|
|data|array/callable|`[]`|x|x|additional data assigned to the asset|
|filters|array|`[]`|x|x|an array of `Inpsyde\Assets\OutputFilter` or callable values to manipulate the output|
|handler|string|`ScriptHandler::class` or `StyleHandler::class`|x|x|The handler which will be used to register/enqueue the Asset|
|localize|array/callable|`[]`|x| |localized array of data attached to scripts|
|inFooter|bool|`true`|x| |defines if the current string is printed in footer|
|media|string|`'all'`| |x|type of media for the style|


## Type of Assets
By default the package comes with predefined types of assets:

|const|hook|location|
|---|---|---|
|`Asset::FRONTEND`|`wp_enqueue_scripts`|Frontend|
|`Asset::BACKEND`|`admin_enqueue_scripts`|Backend| 
|`Asset::LOGIN`|`login_enqueue_scripts`|wp-login.php|
|`Asset::CUSTOMIZER`|`customize_controls_enqueue_scripts`|Customizer|

## Using multiple types
To avoid duplicated registration of Assets in different states such as backend and frontend, it is possible to add multiple types due bitwise operator `|` (OR).

Here's a short example for a CSS-file which will be enqueued in frontend *and* backend:

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
