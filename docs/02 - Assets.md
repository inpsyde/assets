# Assets
There are two main classes delivered:

* `Inpsyde\Assets\Script` - dealing with JavaScript-files.
* `Inpsyde\Assets\Style` - dealing with CSS-files.

Each can receive a configuration injected into it's constructor. Following configurations are available:

|property|type|default|`Script`|`Style`|description|
|----|----|----|----|----|----|
|dependencies|array|`[]`|x|x|all defined depending handles|
|type|string|falls back to either `TYPE_SCRIPT` or `TYPE_STYLE`|x|x|depending on type the `Asset` will enqueued in different locations|
|version|string|`''`|x|x|version of the given asset|
|enqueue|bool/callable|`true`|x|x|is the asset only registered or also enqueued|
|data|array/callable|`[]`|x|x|additional data assigned to the asset|
|filters|array|`[]`|x|x|an array of `Inpsyde\Assets\OutputFilter` or callable values to manipulate the output|
|localize|array/callable|`[]`|x| |localized array of data attached to scripts|
|inFooter|bool|`true`|x| |defines if the current string is printed in footer|
|media|string|`'all'`| |x|type of media for the style|
|handler|string|`ScriptHandler::class` or `StyleHandler::class`|The handler which will be used to register/enqueue the Asset|

## Type of Assets
By default the package comes with predefined types of assets:

|const|hook|class|location|
|---|---|---|---|
|`Asset::FRONTEND`|`wp_enqueue_scripts`|`Style`|Frontend|
|`Asset::BACKEND`|`admin_enqueue_scripts`|`Style`|Backend| 
|`Asset::LOGIN`|`login_enqueue_scripts`|`Style`|wp-login.php|
|`Asset::CUSTOMIZER`|`customize_controls_enqueue_scripts`|`Style`|Customizer|
