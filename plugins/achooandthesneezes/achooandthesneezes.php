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
  let _paq = _paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    let u="//trexthepirate.com/traffic/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 6]);
    let d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<noscript><p><img src="//trexthepirate.com/traffic/piwik.php?idsite=6" style="border:0;" alt="" /></p></noscript>
<!-- End Matomo Code -->
<?php
}
add_action( 'wp_footer', __NAMESPACE__ . '\matomo_analytics' );

function blogroll_shortcode( $atts ) {
	$atts = shortcode_atts( [
		'category_name' => '',
		'title_li' => '',
	], $atts );

	$output = "<div id='{$atts['category_name']}'>";
	$output .= wp_list_bookmarks(
		[
			'category_name' => $atts['category_name'],
			'categorize'    => false,
			'echo'          => false,
			'title_li'      => $atts['title_li'],
		]
	);
	$output .= '</div>';

	return $output;
}
add_shortcode( 'blogroll_shortcode', __NAMESPACE__ . '\blogroll_shortcode' );

function stories_on_frontpage( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    if ( $query->is_front_page() ) {
		$query->set('post_type', [ 'post', 'stories' ] );
    }
}
add_action( 'pre_get_posts', __NAMESPACE__ . '\stories_on_frontpage' );