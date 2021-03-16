# `OutputFilter`

These callbacks are specified to manipulate the output of the `Script` via `script_loader_tag` and `Style`
via `style_loader_tag`.

To use an `OutputFilter` you've to assign them to a specific asset:

```php
<?php
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\OutputFilter\AsyncScriptOutputFilter;

$script = new Script('my-handle', 'script.js', Asset::FRONTEND);
$script = $script->withFilters(AsyncScriptOutputFilter::class);
```

## Available filters

Following default OutputFilters are shipped with this package:

### `AsyncStyleOutputFilter`

This filter will allow you to load your CSS async via `preload`. It also delivers a polyfill for older browsers which is
appended once to ensure that script-loading works properly.

```
<link rel="preload" href="{url}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="{url}" /></noscript>
<script>/* polyfill for older browsers */</script>
```

### `InlineAssetOutputFilter`

This filter allows you to print your `Style` or `Script` inline into the DOM if the file is readable.

### `AttributesOutputFilter`

This filter will be added automatically if you're using `Asset::withAttributes([])` and allows you to set additonal
key-value pairs as attributes to your `script`- or `link`-tag.

See more in [03 - Assets.md](./03 - Assets.md).

### `AsyncScriptOutputFilter` (deprecated)

**[!] deprecated:** Please use instead `Script::withAttributes(['async' => true]);`

### `DeferScriptOutputFilter`  (deprecated)

**[!] deprecated:** Please use instead `Script::withAttributes(['defer' => true]);`

## Create your own filter

You can either implement the `Inpsyde\Assets\OutputFilter\AssetOutputFilter`-interface or just use a normal callable
function which will applied on the `Asset`:

```php
<?php
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Script;

$customFilter = function( string $html, Asset $asset ): string
{
    // do whatever you have to do.

    return $html;
};

$script = new Script('my-handle', 'script.js', Asset::FRONTEND);
$script = $script->withFilters($customFilter);
```
