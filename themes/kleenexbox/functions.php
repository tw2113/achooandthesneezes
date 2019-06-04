<?php

namespace kleenex;

function theme_setup() {
	add_theme_support( 'post-formats', array(
		'aside',
		'audio',
		'gallery',
		'quote',
		'link',
		'image',
		'status',
		'chat',
		'video'
	) );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	register_nav_menus( array( 'primary' => 'Primary Navigation' ) );
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\theme_setup' );

function script_styles() {
	if ( is_admin() ) {
		return;
	}

	//Enqueue styles
	wp_enqueue_style( 'normalize', get_stylesheet_directory_uri() . '/assets/css/normalize.css', null, 'all' );
	wp_enqueue_style( 'style', get_bloginfo( 'stylesheet_url' ), 'normalize', null, 'all' );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\script_styles' );

function page_menu_args( $args ) {
	$args['show_home'] = true;

	return $args;
}
add_filter( 'wp_page_menu_args', __NAMESPACE__ . '\page_menu_args' );

// browser detection via body_class
function browser_body_class( $classes ) {
	//WordPress global vars available.
	global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

	if ( $is_lynx ) {
		$classes[] = 'lynx';
	} elseif ( $is_gecko ) {
		$classes[] = 'firefox';
	} elseif ( $is_opera ) {
		$classes[] = 'opera';
	} elseif ( $is_NS4 ) {
		$classes[] = 'ns4';
	} elseif ( $is_safari ) {
		$classes[] = 'safari';
	} elseif ( $is_chrome ) {
		$classes[] = 'chrome';
	} elseif ( $is_IE ) {
		$classes[] = 'ie';
	} else {
		$classes[] = 'unknown';
	}

	if ( $is_iphone ) {
		$classes[] = 'iphone';
	}

	//Adds a class of singular too when appropriate
	if ( is_singular() && ! is_home() ) {
		$classes[] = 'singular';
	}

	return $classes;
}
add_filter( 'body_class', __NAMESPACE__ . '\browser_body_class' );

function add_post_classes( $classes ) {
	global $wp_query;

	if ( $wp_query->found_posts < 1 ) {
		return $classes;
	}
	if ( $wp_query->current_post === 0 ) {
		$classes[] = 'post-first';
	}

	if ( $wp_query->current_post % 2 ) {
		$classes[] = 'post-even';
	} else {
		$classes[] = 'post-odd';
	}
	if ( $wp_query->current_post === ( $wp_query->post_count - 1 ) ) {
		$classes[] = 'post-last';
	}

	return $classes;
}
add_filter( 'post_class', __NAMESPACE__ . '\add_post_classes' );

add_filter( 'widget_text', 'do_shortcode' );

function posts_columns_attachment_id( $defaults ) {
	$defaults['wps_post_attachments_id'] = __( 'ID' );

	return $defaults;
}
add_filter( 'manage_media_columns', __NAMESPACE__ . '\posts_columns_attachment_id', 1 );

function posts_custom_columns_attachment_id( $column_name, $id ) {
	if ( $column_name === 'wps_post_attachments_id' ) {
		echo $id;
	}
}
add_action( 'manage_media_custom_column', __NAMESPACE__ . '\posts_custom_columns_attachment_id', 1, 2 );

//Add Plugins link to Admin Bar
function plugins_admin_bar_link( $wp_admin_bar ) {
	if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
		return;
	}

	$wp_admin_bar->add_node( array(
		'parent' => 'site-name',
		'id'     => 'ab-plugins',
		'title'  => 'Plugins',
		'href'   => admin_url( 'plugins.php' )
	) );
}
add_action( 'admin_bar_menu', __NAMESPACE__ . '\plugins_admin_bar_link', 35 );

function credits() {
	$tmpl = '<small>&copy;%s <a href="%s" rel="home">%s</a></small>';

	printf(
		$tmpl,
		date( 'Y' ),
		esc_attr( home_url( '/' ) ),
		get_bloginfo( 'name' )
	);
}
