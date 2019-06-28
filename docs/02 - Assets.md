# Assets
There are two main classes delivered:

* `Inpsyde\Assets\Script` - dealing with JavaScript-files.
* `Inpsyde\Assets\Style` - dealing with CSS-files.

Each instance requires a `string $handle`, `string $url`, `int $location` and optionally a configuration via `array $config`. 

## Configuration
### ...via `$config`

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
|media|string|`'all'`| |x|type of media for the `Style`|
|localize|array/callable|`[]`|x| |localized array of data attached to `Script`|
|inFooter|bool|`true`|x| |defines if the current `Script` is printed in footer|
|inline|array|`[]`|x| |allows you to add inline scripts to `Script`-class via `['before' => [], 'after' => []]`|
|translation|array|`[]`|x| |Load translation for `Script`-class via `['path' => string, 'domain' => string]`|


### ...via methods `Inpsyde\Assets\Script`

```php
<?php
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Handler\ScriptHandler;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\OutputFilter\AsyncScriptOutputFilter;

$script = new Script('foo', 'foo.js');
$script
    ->forLocation(Asset::FRONTEND)
    ->withDependencies('wp-elements', 'wp-core', 'wp-i18n')
    ->withVersion('1.0')
    ->useHandler(ScriptHandler::class)
    ->withFilters(AsyncScriptOutputFilter::class)
    ->withFilters(function(string $html, Asset $asset): string {
        return $html;
    })
    ->withCondition('lt IE 9')
    ->withTranslation('domain', '/path/to/json/file/')
    ->withLocalize('foo', ['multiple values'])
    ->withLocalize('bar', function() {
        return 'other value';
    })
    ->isInFooter()
    ->isInHeader()
    ->appendInlineScript('var foo = "bar";')
    ->prependInlineScript('var baz = "bam"')
    ->useDeferFilter()
    ->useAsyncFilter();
```

### ...via methods `Inpsyde\Assets\Style`

```php
<?php
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Handler\StyleHandler;
use Inpsyde\Assets\OutputFilter\AsyncStyleOutputFilter;

$style = new Style('foo', 'foo.css');
$style
    ->forLocation(Asset::FRONTEND)
    ->withDependencies('foo', 'bar', 'baz')
    ->withVersion('1.0')
    ->useHandler(StyleHandler::class)
    ->withFilters(AsyncStyleOutputFilter::class)
    ->withFilters(function(string $html, Asset $asset): string {
        return $html;
    })
    ->withCondition('lt IE 9')
    ->withInlineStyles('body { background-color: #000; }')
    ->useAsyncFilter();
```

## Asset locations
By default the package comes with predefined locations of assets:

|const|hook|location|
|---|---|---|
|`Asset::FRONTEND`|`wp_enqueue_scripts`|Frontend|
|`Asset::BACKEND`|`admin_enqueue_scripts`|Backend| 
|`Asset::LOGIN`|`login_enqueue_scripts`|wp-login.php|
|`Asset::CUSTOMIZER`|`customize_controls_enqueue_scripts`|Customizer|
|`Asset::BLOCK_EDITOR_ASSETS`|`enqueue_block_editor_assets`|Gutenberg Editor|

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
