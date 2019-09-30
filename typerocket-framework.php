<?php
/*
Plugin Name: TypeRocket Framework 4
Plugin URI: https://typerocket.com/
Description: TypeRocket Framework - A WordPress Framework To Empower Your Development
Version: 4.0.8
Author: Robojuice
Author URI: https://robojuice.com/
License: GPLv3 or later
*/
defined( 'ABSPATH' ) or die( 'Nothing here to see!' );

// TypeRocket Framework
class TypeRocket_Framework {

    public $path = null;
    public $message = '';
    public $activating = false;
    public $id = 'settings_typerocket';

    function __construct()
    {
        tr_auto_loader();

        new \TypeRocket\Updates\PluginUpdater([
            'slug' => 'typerocket-framework',
            'api_url' =>  'https://typerocket.com/plugins/typerocket-framework/'
        ]);

        $this->path = plugin_dir_path(__FILE__);
        define('TR_AUTO_LOADER', '__return_false');
        define('TR_PLUGIN_VERSION', '4.0.8');
        register_activation_hook( __FILE__, array($this, 'activation') );
        add_action('admin_notices',  array($this, 'activation_notice') );
        add_action('plugins_loaded', array($this, 'plugins_loaded'));
    }

    function activation() {
        $this->activating = true;
        flush_rewrite_rules();
        set_transient( 'typerocket-admin-notice' , true );
    }

    function activation_notice() {
        if( get_transient( 'typerocket-admin-notice' ) && ! $this->activating ) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>TypeRocket wants you to <a href="<?php menu_page_url($this->id, true); ?>">check your TypeRocket settings</a> to validate your installation is correct.</p>
            </div>
            <?php
        }
    }

    function plugins_loaded() {
        require 'typerocket/init.php';

        if(!defined('TR_PLUGIN_ADMIN')) {
            define('TR_PLUGIN_ADMIN', true);
        }

        if(TR_PLUGIN_ADMIN) {
            $settings = [
                'view_file' => __DIR__ . '/admin.php',
                'menu' => 'TypeRocket',
                'capability' => 'activate_plugins'
            ];
            (new \TypeRocket\Register\Page('settings', __('TypeRocket'), __('TypeRocket Settings'), $settings))
                ->addToRegistry();
        }
    }
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

if(!function_exists('tr_plugin_plugins')) {
    function tr_plugin_plugins() {

        $plugins = [
            '\TypeRocketSEO\Plugin',
            '\TypeRocketThemeOptions\Plugin',
        ];

        if(defined('TR_PLUGIN_PAGE_BUILDER')) {
            $plugins[] = '\TypeRocketPageBuilder\Plugin';
        }

        return $plugins;
    }
}

if(!function_exists('tr_plugin_gutenberg')) {
    function tr_plugin_gutenberg($value = true) {

        if(defined('TR_PLUGIN_PAGE_BUILDER') && $value) {
            if(TR_PLUGIN_PAGE_BUILDER) {
                $value = ['post'];
            }
        }

        return $value;
    }
}

if(!function_exists('tr_plugin_config_paths')) {
    function tr_plugin_config_paths() {
        $temp_uri = get_template_directory_uri();
        $temp_dir = get_template_directory();
        $app_path = TR_PATH . '/app';

        if(file_exists( $temp_dir . '/app/Http/Kernel.php')) {
            $app_path = $temp_dir . '/app';
        }

        return [
            'urls' => [
                'assets' => plugins_url( '/typerocket/wordpress/assets/', TR_PATH ),
                'components' => $temp_uri . '/wordpress/assets/components',
            ],
            'base'  => TR_PATH,
            'resources'  => $temp_dir . '/resources',
            'views'  => $temp_dir . '/resources/views',
            'pages'  => $temp_dir . '/resources/pages',
            'visuals'  => $temp_dir . '/resources/visuals',
            'components'  => $temp_dir . '/resources/components',
            'plugins' => $temp_dir . '/plugins',
            'app'  => $app_path,
            'themes'  => $temp_dir . '/resources/themes',
            'migrate'  => [
                'driver' => 'file',
                'migrations' => [
                    TR_PATH . '/sql/migrations',
                ],
                'run' => TR_PATH . '/sql/run',
            ]

        ];
    }
}

function tr_auto_loader() {

    include "typerocket/vendor/typerocket/core/functions/functions.php";
    include "typerocket/vendor/typerocket/core/functions/helpers.php";

    if(!defined('TR_WP_PLUGIN_APP_MAP')) {

        $temp_dir = get_template_directory();
        $app_path = __DIR__ . '/typerocket/app/';

        if(file_exists( $temp_dir . '/app/Http/Kernel.php')) {
            $app_path = $temp_dir . '/app/';
        }

        $map_app = [
            'prefix' => 'App\\',
            'folder' => $app_path,
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

new TypeRocket_Framework();
