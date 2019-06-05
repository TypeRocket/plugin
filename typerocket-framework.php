<?php
/*
Plugin Name: TypeRocket Framework 4
Plugin URI: https://typerocket.com/
Description: TypeRocket Framework - A WordPress Framework To Empower Your Development
Version: 4.0.0
Author: Robojuice
Author URI: https://robojuice.com/
License: GPLv3 or later
*/
defined( 'ABSPATH' ) or die( 'Nothing here to see!' );

// Allow only one version of TypeRocket
if(defined('TR_PATH')) {
    return;
}

function tr_autoload_psr4(array &$map = []) {
    if(isset($map['init'])) {
        foreach ($map['init'] as $file) {
            require $file;
        }
    }
    spl_autoload_register(function ($class) use (&$map) {
        if (isset($map['map'][$class])) {
            require $map['map'][$class];
            return;
        }
        $prefix = $map['prefix'];
        $folder = $map['folder'];
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) { return; }
        $file = $folder . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $len)) . '.php';
        if ( is_file($file) ) {
            require $file;
            return;
        }
    });
}

function tr_auto_loader() {

    include "typerocket/vendor/typerocket/core/functions/functions.php";
    include "typerocket/vendor/typerocket/core/functions/helpers.php";

    if(!defined('TR_WP_PLUGIN_APP_MAP')) {
        $map_app = [
            'prefix' => 'App\\',
            'folder' => __DIR__ . '/typerocket/app/',
        ];
    } else {
        $map_app = TR_WP_PLUGIN_APP_MAP;
    }

    tr_autoload_psr4($map_app);

    $map_core = [
        'prefix' => 'TypeRocket\\',
        'folder' => __DIR__ . '/typerocket/vendor/typerocket/core/src/',
    ];
    tr_autoload_psr4($map_core);

    $map_theme = [
        'prefix' => 'TypeRocketThemeOptions\\',
        'folder' => __DIR__ . '/typerocket/vendor/typerocket/plugin-theme-options/src/',
    ];
    tr_autoload_psr4($map_theme);

    $map_dev = [
        'prefix' => 'TypeRocketDev\\',
        'folder' => __DIR__ . '/typerocket/vendor/typerocket/plugin-dev/src/',
    ];
    tr_autoload_psr4($map_dev);

    $map_builder = [
        'prefix' => 'TypeRocketPageBuilder\\',
        'folder' => __DIR__ . '/typerocket/vendor/typerocket/plugin-builder/src/',
    ];
    tr_autoload_psr4($map_builder);

    $map_seo = [
        'prefix' => 'TypeRocketSEO\\',
        'folder' => __DIR__ . '/typerocket/vendor/typerocket/plugin-seo/src/',
    ];
    tr_autoload_psr4($map_seo);
}

define('TR_AUTO_LOADER', 'tr_auto_loader');

add_action('plugins_loaded', function() {
    require 'typerocket/init.php';
});