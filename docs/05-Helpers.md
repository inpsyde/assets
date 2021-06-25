# Helpers

The `inpsyde/assets`-Package comes with some useful helper functions.

## Asset Suffix

The function `Inpsyde\Assets\assetSuffix` allows to automatically suffix the given Asset with `.min` if `SCRIPT_DEBUG === false`:

```php
<?php

use function Inpsyde\Assets\withAssetSuffix;

$fileName = withAssetSuffix('my-script.js');

// if SCRIPT_DEBUG === false -> my-script.min.js
// if SCRIPT_DEBUG === true -> my-script.js 
``` 


## Symlink an Asset-folder

Sometimes your Assets will not be inside the web-root, like Composer packages which are normally published outside of web-root.
Therefor you can use a simple helper `Inpsyde\Assets\symlinkedAssetFolder` which allows you to move your asset-folder inside the web-root:

```php
<?php

use function Inpsyde\Assets\symlinkedAssetFolder;

$assetDirUrl = symlinkedAssetFolder('/full/path/to/assets/', 'my-package');
// will return: https://www.example.com/wp-content/~inpsyde-assets/my-package/
```
