<?php
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

delete_transient( 'typerocket-admin-notice' );

if(empty(get_option('rewrite_rules'))) {
    $url = admin_url('options-permalink.php');
    ?>
    <div class="notice notice-error is-dismissible">
        <?php _e('<p>Enable pretty permalinks under your <a href="'.$url.'">admin "Permalink Settings" page</a>. Not sure what permalinks are? <a href="https://wordpress.org/support/article/using-permalinks/">Learn about pretty permalinks</a>.</p>', 'typerocket-domain'); ?>
    </div>
    <?php
}

$tabs = tr_tabs()->layoutLeft();
$tabs->tab('About', 'rocket', function() {
    ?>
    <div class="tr-p-20">
        <?php echo \TypeRocket\Html\Element::title('TypeRocket Open'); ?>
        <a class="button button-primary button-hero" target="_blank" href="https://typerocket.com/pro/">Get TypeRocket Pro</a>
        <p class="hide-if-no-customize">or, <a target="_blank" href="https://typerocket.com/docs/v5/">get started</a>.</p>
        <h3>First Steps</h3>
        <p><?php _e('We’ve assembled some links to get you started:'); ?></p>
        <ul>
            <li><i class="dashicons dashicons-email"></i> <a target="_blank" href="https://us8.list-manage.com/subscribe?u=7bbb7409e86c85970f6150c5e&id=1d45a226d0">Join the TypRocket Mailing List.</a></li>
            <li><i class="dashicons dashicons-youtube"></i> <a target="_blank" href="https://www.youtube.com/watch?v=yqGiHCuDohQ&list=PLh6jokL0yBPT6uJPnMFcZJJ1PzNs8XaK8">Watch our MVC Video series.</a></li>
            <li><i class="dashicons dashicons-admin-post"></i> <a target="_blank" href="https://typerocket.com/docs/v5/post-types-making/">Add your first post type.</a></li>
            <li><i class="dashicons dashicons-admin-page"></i> <a target="_blank" href="https://typerocket.com/docs/v5/extension-page-builder/">Learn the page builder.</a></li>
        </ul>
    </div>
    <?php
})->setDescription('Getting started');

$tabs->tab('Configure', 'gear', function() {
    ?>
    <div class="tr-p-20">
        <?php echo \TypeRocket\Html\Element::title('Configuration'); ?>
        <p>The <strong>TypeRocket Pro</strong> WordPress plugin can be further configured in your <code>wp-config.php</code> file.</p>
        <ul>
            <li>To disable auto updates: <code>define('TYPEROCKET_UPDATES', false);</code></li>
            <li>To disable page builder: <code>define('TYPEROCKET_PAGE_BUILDER', false);</code></li>
            <li>To disable post types UI: <code>define('TYPEROCKET_UI', false);</code></li>
        </ul>
    </div>
    <?php
})->setDescription('Config settings');

echo $tabs;