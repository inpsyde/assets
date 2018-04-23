# `AssetFactory`
Instead of creating instances by hand, it's sometimes easier to use configuration via array or file to manage your specific assets.

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

In your application you can create all assets from that file by using the `Inpsyde\Assets\AssetFactory`:

```php
<?php
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\AssetFactory;

add_action( 
	AssetManager::ACTION_SETUP, 
	function(AssetManager $assetManager) {
		$assetManager->register(
			...AssetFactory::createFromFile('config/assets.php')
		);
	}
);
```
