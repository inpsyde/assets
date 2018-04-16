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

## Type of Assets
By default the package comes with predefined types of assets:

|const|hook|class|location|
|---|---|---|---|
|`Asset::TYPE_STYLE`|`wp_enqueue_scripts`|`Style`|Frontend|
|`Asset::TYPE_ADMIN_STYLE`|`admin_enqueue_scripts`|`Style`|Backend| 
|`Asset::TYPE_LOGIN_STYLE`|`login_enqueue_scripts`|`Style`|wp-login.php|
|`Asset::TYPE_CUSTOMIZER_STYLE`|`customize_controls_enqueue_scripts`|`Style`|Customizer|
|`Asset::TYPE_SCRIPT`|`wp_enqueue_scripts`|`Script`|Frontend|
|`Asset::TYPE_ADMIN_SCRIPT`|`admin_enqueue_scripts`|`Script`|Backend|
|`Asset::TYPE_LOGIN_SCRIPT`|`login_enqueue_scripts`|`Script`|wp-login.php|
|`Asset::TYPE_CUSTOMIZER_SCRIPT`|`customize_controls_enqueue_scripts`|`Script`|Customizer|