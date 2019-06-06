<?php
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

delete_transient( 'typerocket-admin-notice' );
$rules = get_option('rewrite_rules');

if(empty($rules)) {
    ?>
    <div class="notice notice-error is-dismissible">
        <p>Enable <a href="https://wordpress.org/support/article/using-permalinks/">Pretty Permalinks</a> under <a href="/wp-admin/options-permalink.php">Permalink Settings</a>. "Pretty Permalinks" are required for TypeRocket to work.</p>
    </div>
    <?php
}
?>

<div id="welcome-panel" class="welcome-panel">
    <div class="welcome-panel-content">
        <h2>Welcome to TypeRocket 4!</h2>
        <p class="about-description">We’ve assembled some links to get you started:</p>
        <div class="welcome-panel-column-container">
            <div class="welcome-panel-column">
                <h3>Get Started</h3>
                <a class="button button-primary button-hero" href=" https://typerocket.com/getting-started/">Learn The Basics</a>
                <p class="hide-if-no-customize">or, <a href="https://plugin.tr/wp-admin/customize.php?autofocus[panel]=themes">read the documentation</a></p>
            </div>
            <div class="welcome-panel-column">
                <h3>Next Steps</h3>
                <ul>
                    <li><a href="https://typerocket.com/docs/v4/post-types-making/" class="welcome-icon welcome-add-page">Add your first post type</a></li>
                    <li><a href="https://plugin.tr/wp-admin/post-new.php?post_type=page" class="welcome-icon welcome-write-blog">Add an About page</a></li>
                    <li><a href="https://plugin.tr/wp-admin/customize.php?autofocus[section]=static_front_page" class="welcome-icon welcome-setup-home">Set up your homepage</a></li>
                    <li><a href="https://plugin.tr/" class="welcome-icon welcome-view-site">View your site</a></li>
                </ul>
            </div>
            <div class="welcome-panel-column welcome-panel-last">
                <h3>More Actions</h3>
                <ul>
                    <li><div class="welcome-icon welcome-widgets-menus">
                            Manage <a href="https://plugin.tr/wp-admin/widgets.php">widgets</a> or <a href="https://plugin.tr/wp-admin/nav-menus.php">menus</a>			</div></li>
                    <li><a href="https://plugin.tr/wp-admin/options-discussion.php" class="welcome-icon welcome-comments">Turn comments on or off</a></li>
                    <li><a href="https://codex.wordpress.org/First_Steps_With_WordPress" class="welcome-icon welcome-learn-more">Learn more about getting started</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php

$icons = function()
{
    $icons = \TypeRocket\Core\Config::locate('app.class.icons');
    $icons = new $icons;
    $generator = new \TypeRocket\Html\Generator();

    echo '<h3><i class="tr-icon-droplet"></i> ' . __('Icons') . '</h3>';
    echo '<p>' . __('These can be used with custom post types and admin pages.');
    echo '</p><p><input onkeyup="trDevIconSearch()" placeholder="' . __('Enter text to search list...') . '" id="dev-icon-search" /></p><ol id="debug-icon-list">';
    foreach ($icons as $k => $v) {
        echo $generator->newElement( 'li', ['class' => 'tr-icon-' . $k, 'id' => $k],
            '<strong>' . $k . '</strong><em>.tr-icon-' . $k . '</em>' )->getString();
    }
    echo '</ol>';
    ?>
    <script language="JavaScript">
        function trDevIconSearch() {
            var input, filter, ul, li, a, i;
            input = document.getElementById("dev-icon-search");
            filter = input.value.toUpperCase();
            ul = document.getElementById("debug-icon-list");
            li = ul.getElementsByTagName("li");
            for (i = 0; i < li.length; i++) {
                a = li[i];
                if (a.id.toUpperCase().indexOf(filter) > -1) {
                    li[i].style.display = "";
                } else {
                    li[i].style.display = "none";
                }
            }
        }
    </script>
    <?php
};

$configure = function() {
    include __DIR__ . '/admin-configure.php';
};

$rules = function() {
    echo '<h3><i class="tr-icon-link"></i> ' . __('Rewrite Rules') . '</h3>';
    $rules = get_option('rewrite_rules');
    if(!empty($rules)) {
        echo "<p>If you are using TypeRocket custom routes they will not appear in this list. TypeRocket detects custom routes on the fly.</p>";
        echo '<table class="wp-list-table widefat fixed striped">';
        echo "<thead><tr><th>" . __('Rewrite Rule') . "</th><th>" . __('Match') . "</th></tr></thead>";
        foreach ($rules as $rule => $match) {
            echo "<tr><td>$rule</td><td>$match</td></tr>";
        }
        echo '</table>';
    } else {
        echo '<p>Enable "Pretty Permalinks" under <a href="/wp-admin/options-permalink.php">Permalink Settings</a>.</p>';
    }
};

$tabs = tr_tabs();
$tabs->addTab(__('Icons'), $icons)
    ->addTab(__('Rewrite Rules'), $rules)
    ->addTab(__('Configure'), $configure)
    ->render('box');