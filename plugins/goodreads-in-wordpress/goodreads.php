<?php
/**
 * Plugin loader.
 *
 * @package Goodreads WordPress Widget
 * @since 1.0.0
 */

/*
 * Plugin Name: Goodreads In WordPress
 * Plugin URI: http://michaelbox.net/
 * Description: Displays Goodreads information.
 * Version: 1.0.0
 * Author: Michael Beckwith
 * Author URI: http://michaelbox.net
 * License: WTFPL
 * Text Domain: mb_goodreads
 */

/*
		DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
			Version 2, December 2004

Copyright (C) 2004 Sam Hocevar <sam@hocevar.net>

Everyone is permitted to copy and distribute verbatim or modified
copies of this license document, and changing it is allowed as long
as the name is changed.

			DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

0. You just DO WHAT THE FUCK YOU WANT TO.

*/

namespace tw2113;

/**
 * Register widgets.
 *
 * @since 1.0.0
 */
function goodreads_register_widget() {
	register_widget( 'tw2113\Goodreads_Current_Reading_Widget' );
	register_widget( 'tw2113\Goodreads_Profile_Widget' );
}
add_action( 'widgets_init', __NAMESPACE__ . '\goodreads_register_widget' );

/**
 * Register and load our textdomain.
 */
function goodreads_widget_init() {

	require_once 'classes/class-goodreads-settings.php';
	require_once 'classes/class-goodreads-api.php';
	require_once 'classes/class-book.php';
	require_once 'classes/class-current-reading-shelf-api.php';
	require_once 'classes/class-goodreads-profile-api.php';

	require_once 'widgets/class-goodreads-base-widget.php';
	require_once 'widgets/class-goodreads-current-reading-widget.php';
	require_once 'widgets/class-goodreads-profile-widget.php';
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\goodreads_widget_init' );
