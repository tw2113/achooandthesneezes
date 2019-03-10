<?php

/*
 * Plugin Name: Achoo and the Sneezes Customizations
 * Plugin URI: Plugin URI
 * Description: Various customizations by Michael
 * Version: 1.0.0
 * Author: Maintainn
 * Author URI: http://www.maintainn.com
 * License: GPLv2
 * Text Domain: Text domain to use
 */

namespace tw2113;

function matomo_analytics() {

if ( is_user_logged_in() ) {
	return;
}
?>
<!-- Matomo -->
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//trexthepirate.com/traffic/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 6]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<noscript><p><img src="//trexthepirate.com/traffic/piwik.php?idsite=6" style="border:0;" alt="" /></p></noscript>
<!-- End Matomo Code -->
<?php
}
add_action( 'wp_footer', __NAMESPACE__ . '\matomo_analytics' );
