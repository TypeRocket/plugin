<?php
/*
|--------------------------------------------------------------------------
| TypeRocket by Robojuice
|--------------------------------------------------------------------------
|
| TypeRocket is designed to work with WordPress 5.2+ and PHP 7+. You
| must initialize it exactly once. We suggest including TypeRocket
| from your theme and let plugins access TypeRocket from there.
|
| Happy coding!
|
| http://typerocket.com
|
*/

if(!defined('TR_PATH')) {
    define('TR_PATH', __DIR__ );
}

// Define App Namespace
if(!defined('TR_APP_NAMESPACE')) {
    define('TR_APP_NAMESPACE', 'App');
}

// Define Config
if(!defined('TR_CORE_CONFIG_PATH')) {

    $temp_dir = get_template_directory();

    if( file_exists($temp_dir . '/config/typerocket.php') ) {
        define('TR_CORE_CONFIG_PATH', $temp_dir . '/config' );
    } else {
        define('TR_CORE_CONFIG_PATH', __DIR__ . '/config' );
    }
}

new \TypeRocket\Core\Config( TR_CORE_CONFIG_PATH );

// Load TypeRocket
if( defined('WPINC') ) {
    ( new \TypeRocket\Core\Launcher() )->initCore();
}