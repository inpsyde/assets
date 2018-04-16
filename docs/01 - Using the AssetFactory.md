# `AssetFactory`
Instead of creating instances by hand, it's sometimes easier to use configuration via array or files to manage your specific assets.

**config/assets.php**
```php
<?php
use Inpsyde\Assets\Asset;

return [
    [
        'handle' => 'foo',
        'url' => 'example.com/assets/foo.css',
        'type' => Asset::TYPE_STYLE, 
    ],
    [
        'handle' => 'bar',
        'url' => 'example.com/assets/bar.js',
        'type' => Asset::TYPE_SCRIPT, 
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