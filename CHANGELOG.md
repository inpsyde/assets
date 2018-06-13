# Changelog

## 1.2
### New
- Added new locations to support registration of assets to Gutenberg:
   - `Asset::BLOCK_EDITOR_ASSETS` - triggered when Gutenberg Editor is loading.
   - `Asset::BLOCK_ASSETS` - triggered when Gutenberg Editor is loading *and* on frontend.

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
