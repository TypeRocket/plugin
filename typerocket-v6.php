<?php
/*
Plugin Name: TypeRocket - Antennae
Plugin URI: https://typerocket.com/
Description: TypeRocket is a framework that joins refined UI elements and modern programming architecture together.
Version: 6.0.0
Requires at least: 6.1
Requires PHP: 8.0.2
Author: TypeRocket
Author URI: https://typerocket.com/
License: GPLv3 or later
*/
namespace TypeRocket;

defined( 'ABSPATH' ) or die( 'Nothing here to see!' );

final class OpenPlugin6
{
    protected $path = null;
    protected $message = '';
    protected $activating = false;
    protected $id = 'settings_typerocket';

    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'plugins_loaded'], 13);
        register_activation_hook( __FILE__, [$this, 'activation']);
    }

    public function plugins_loaded()
    {
        if(defined('TYPEROCKET_PLUGIN_INSTALL') || defined('TYPEROCKET_PATH')) {
            add_filter('plugin_action_links', function($actions, $plugin_file) {
                if( strpos(__FILE__, $plugin_file) ) {
                    $actions['settings'] = '<span style="color: red">Inactive Install</span>';
                }

                return $actions;
            }, 10, 2 );

            return;
        }

        define('TYPEROCKET_PLUGIN_VERSION', '6.0.0');
        define('TYPEROCKET_PLUGIN_INSTALL', __DIR__);
        define('TYPEROCKET_PLUGIN_PRO', false);

        if(!defined('TYPEROCKET_ROOT_WP'))
            define('TYPEROCKET_ROOT_WP', ABSPATH);

        $this->loadConfig();
        require 'typerocket/init.php';

        if(typerocket_env('TYPEROCKET_UPDATES', true)) {
            add_filter('http_request_host_is_external', function($value, $host) {
                return $value || $host == 'typerocket.com';
            }, 10, 2);

            new \TypeRocket\OpenPlugin6\Updater([
                'slug' => 'typerocket-v6',
                'api_url' => 'https://typerocket.com/plugins/typerocket-v6/'
            ]);
        }

        $this->path = plugin_dir_path(__FILE__);
        define('TYPEROCKET_AUTO_LOADER', '__return_false');
        add_action('admin_notices',  [$this, 'activation_notice']);
        add_action('typerocket_loaded', [$this, 'typerocket_loaded']);
        add_filter('plugin_action_links', [$this, 'links'], 10, 2 );
    }

    public function links($actions, $plugin_file)
    {
        if( strpos(__FILE__, $plugin_file) ) {
            $url = menu_page_url($this->id, false);
            $actions['settings'] = '<a href="'.$url.'" aria-label="TypeRocket Settings">Settings</a>';
            $actions['pro'] = '<a target="_blank" href="https://typerocket.com/pro/" aria-label="TypeRocket Pro">Get Pro</a>';
        }

        return $actions;
    }

    public function loadConfig()
    {
        if( defined('TYPEROCKET_OVERRIDE_PATH') ) {
            $temp_dir = TYPEROCKET_OVERRIDE_PATH;
        } else {
            $temp_dir = get_template_directory();
        }

        // maybe get config from theme
        if( !defined('TYPEROCKET_CORE_CONFIG_PATH') ) {
            if(file_exists( $temp_dir . '/config/galaxy.php')) {
                define('TYPEROCKET_CORE_CONFIG_PATH', $temp_dir . '/config' );
            }
        }

        // maybe get app from theme
        if(!defined('TYPEROCKET_AUTOLOAD_APP')) {
            if(file_exists( $temp_dir . '/app/Http/Kernel.php')) {
                if(!defined('TYPEROCKET_APP_NAMESPACE') ) {
                    define('TYPEROCKET_APP_NAMESPACE', 'App');
                }

                define('TYPEROCKET_APP_ROOT_PATH', $temp_dir);
                define('TYPEROCKET_ALT_PATH', $temp_dir);
                define('TYPEROCKET_AUTOLOAD_APP', [
                    'prefix' => TYPEROCKET_APP_NAMESPACE . '\\',
                    'folder' => $temp_dir . '/app/',
                ]);
            }
        }
    }

    public function activation()
    {
        $this->activating = true;
        flush_rewrite_rules();
        set_transient( 'typerocket-admin-notice' , true );
    }

    public function activation_notice()
    {
        $page = $_GET['page'] ?? null;
        if( $this->id != $page && get_transient( 'typerocket-admin-notice' ) && ! $this->activating ) {
            $url = menu_page_url($this->id, false);
            $alert = __("TypeRocket wants you to <a href=\"{$url}\">check your TypeRocket settings</a> to validate your installation is correct.", 'typerocket-domain')
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php echo $alert; ?></p>
            </div>
            <?php
        }
    }

    public function typerocket_loaded()
    {
        tr_page('settings@TypeRocket\\OpenPlugin6\\SettingsController', 'typerocket', __('TypeRocket Settings'), [
            'menu' => __('TypeRocket'),
            'capability' => 'activate_plugins'
        ]);

        tr_meta_box('Upgrade To TypeRocket Pro')->setCallback(function() {
            echo \TypeRocket\Template\View::new(__DIR__ . '/meta-dashboard.php')->setEngine(\TypeRocket\Template\TemplateEngine::class);
        })->addScreen('dashboard');
    }
}

new OpenPlugin6();