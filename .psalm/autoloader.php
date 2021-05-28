<?php // phpcs:disable
if (defined('ABSPATH')) {
    return;
}

define('ABSPATH', './vendor/wordpress/wordpress/');
define('WPINC', 'wp-includes');
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');

require_once ABSPATH . WPINC .'/load.php';
require_once ABSPATH . WPINC .'/plugin.php';
require_once ABSPATH . WPINC . '/default-constants.php';
require_once ABSPATH . WPINC . '/link-template.php';
require_once ABSPATH . WPINC . '/formatting.php';
require_once ABSPATH . WPINC . '/functions.php';
require_once ABSPATH . WPINC . '/theme.php';
require_once ABSPATH . WPINC . '/functions.wp-styles.php';
require_once ABSPATH . WPINC . '/functions.wp-scripts.php';


require_once ABSPATH . WPINC . '/class.wp-dependencies.php';
require_once ABSPATH . WPINC . '/class.wp-scripts.php';
require_once ABSPATH . WPINC . '/class.wp-styles.php';


