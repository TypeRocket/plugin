<h3><i class="tr-icon-cog"></i> Configure</h3>
<p>The <strong>TypeRocket Framework 4</strong> WordPress plugin can be further configured in your <code>wp-config.php</code> file.</p>
<ul>
    <li>To disable this page add: <code>define('TR_PLUGIN_ADMIN', false);</code></li>
    <li>To disable auto updates: <code>define('TR_UPDATES_OFF', true);</code></li>
    <li>You can define your enabled TypeRocket plugins by overriding the <code>tr_plugin_plugins()</code> function.</li>
    <li>You can enable the page builder plugin using the <code>define('TR_PLUGIN_PAGE_BUILDER', true);</code> constant.</li>
</ul>