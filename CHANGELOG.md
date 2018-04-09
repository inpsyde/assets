# Changelog

## 0.2
### Breaking Changes
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
