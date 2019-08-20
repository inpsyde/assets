# Changelog

## 2.0
### New

* Added [codecov](https://codecov.io) badget to `README.md` and `travis.yml`.
* Implementation of Loaders to create Assets from various different input resources:
  * `EncoreEntrypointsLoader` - loading Assets and it's dependencies from [Symfony's Encore](https://symfony.com/doc/current/frontend/encore/installation.html) `entrypoints.json`.
  * `WebpackManifestLoader` - loading Assets from Webpack generated `manifest.json`
  * `PhpFileLoader` - moved from `AssetFactory::createFromFile()`
  * `ArrayLoader` - moved from `AssetFactory::createFromArray()`
* Added method `Asset::withDependencies` to interface.

### Deprecations
* `AssetFactory::createFromFile` is now deprecated. Use `(new PhpFileLoader())->load($resource)`
* `AssetFactory::createFromArray` is now deprecated. Use `(new ArrayLoader())->load($resource)`

## Breaking Change
* Removed `AssetFactory::migrateConfig` which was introduced in 1.1.
 
## 1.4
### New

* Added `OutputFilter\InlineAssetOutputFilter` to allow printing the Asset inline.

#### Automatically discovering Asset version
Added new automatic version handling based on the `filemtime`. This can be enabled/disabled via
* `BaseAsset::enableAutodiscoverVersion`
* `BaseAsset::disableAutodiscoverVersion`

By default, this is enabled. When no `Asset::withFilePath` or `filePath` configuration is given, the `BaseAsset` tries to solve the path to the file automatically. 

Added new methods: 
* `Asset::withFilePath` - set a filePath which s used in automatic discovering the version
* `Asset::filePath` - will automatically discover the filePath if not set

#### AssetManager
* Added `AssetManager::asset(string $handle, string $type)` to access registered assets.

#### PHP Version
* Moved to PHPUnit 7.*.
* Moved PHP min version to 7.1.

#### Style/Script mutable
* `Script` and `Style` are now mutable. See [Assets.md](https://github.com/inpsyde/assets/blob/1.4/docs/02%20-%20Assets.md).

#### Style/Script new API features
* `Script` now supports ...
    * inline append/prepend scripts.
    * translations for Gutenberg Blocks.
    * set directly `Script::useAsyncFilter()` or `Script::useDeferFilter()` to attach filter.
* `Style` now supports ...   
    * append inline styles
    * set directly `Style::useAsyncFilter()` to attach filter.

### Script localization
Script localization accepts now multiple closures as objectValues:

```php
<?php
use Inpsyde\Assets\Script;

$script = new Script('handle', 'handle.js');
$script
	->withLocalize('objectName', 'objectValue')
	->withLocalize('posts', function(): array {
		return get_posts(['post_type' => 'page']);
	})
	->withLocalize('foo', function(): string {
	    return 'bar';
	});
```


----

## 1.3.1
### Fixes
- Fixed values for bitwise compare, to dont sum up and overlap.

## 1.3 
### New
- Moved `AssetManager::currentHooks` into `AssetHookResolver::resolve`.
- Added new constants for available hooks to `Asset`.
- This project has now GPL-2.0-or-later as license.

----

## 1.2.2
### Fixes
- Added missing check for hooks with "New Post" (`post-new.php`) for Gutenberg available styles/scripts.

## 1.2.1
### Fixes
- Fixed `AssetManager::currentHooks` by returning now an array of hooks to support also Gutenberg enqueues.

## 1.2
### New
- Added new locations to support registration of assets to Gutenberg:
   - `Asset::BLOCK_EDITOR_ASSETS` - triggered when Gutenberg Editor is loading.
   - `Asset::BLOCK_ASSETS` - triggered when Gutenberg Editor is loading *and* on frontend.

----

## 1.1.1

### Fixes
- Execute `OutputFilterAwareAssetHandler::filter` on registered assets as well.

### Improvements
- Updated some doc blocks.

## 1.1

### Improvements
- Renamed `Inpsyde\Assets\Asset::type` to `Inpsyde\Assets\Asset::location` to be more clear.
- `Inpsyde\Assets\AssetFactory` - changed configuration keys...
    - `type` to `location`
    - `class`to `type`
- `Inpsyde\Assets\AssetFactory` - added migration to new configuration keys to avoid breaking change for now.

## 1.0
### Breaking changes
- Removed `Inpsyde\Assets\assetManager()`-function. Function is replaced by a WordPress hook to setup assets. See [Migration](./docs/99 - Migration.md).
- Renamed all flags in `Inpsyde\Assets\Asset` to match the different locations.

### Improvements
- Added `inc/bootstrap.php` to setup the `AssetManager` not to early and allow Plugins/Themes to start with a hook instead of using a function.
- Setup of default handlers are now in the callback hook and only if at least one asset is found.
- Added `'class'`-option to configuration for `Inpsyde\Assets\AssetFactory::create`.
- Added `Inpsyde\Assets\Asset::handler` which now allows to implement custom Handlers.
- Added support for multiple `Asset::type()` via bitwise `|` (OR) to register Assets in different locations only once.
 
### Fixes
- Fix wrong hook returned for customizer.

----

## 0.2
### Breaking changes
- Removed `Inpsyde\Assets\assetFactory()`-function. The `Inpsyde\Assets\AssetFactory` has now static methods.
- Renamed `inc/bootstrap.php` to `inc/functions.php`.

### Improvements
- Move `OutputFilter` to specific `AssetHandler`.
- Added new type of `Asset`:
    - `TYPE_ADMIN_STYLE`
    - `TYPE_LOGIN_STYLE`
    - `TYPE_CUSTOMIZER_STYLE`
    - `TYPE_ADMIN_SCRIPT`
    - `TYPE_LOGIN_SCRIPT`
    - `TYPE_CUSTOMIZER_SCRIPT`
- Added now detection of current "screen" to display new types of `Asset`.
- Moved `AssetManager::processFilters` to own interface `OutputFilterAwareAssetHandler` and into specific `Handler` via `OutputFilterAwareAssetHandlerTrait` to avoid wrong usage of Style-/Script-OutputFilters. 

## 0.1
- First Release.
