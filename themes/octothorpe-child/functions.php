<?php

namespace tw2113;

function achoo_setup() {
	register_nav_menus( [ 'sidebar' => 'Sidebar Navigation' ] );
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\achoo_setup' );

function achoo_assets() {
	wp_enqueue_style( 'achoo', get_stylesheet_directory_uri() . '/assets/css/sneeze.css', [ 'octothorpe-style' ] );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ .'\achoo_assets' );

add_filter( 'pre_option_link_manager_enabled', '__return_true' );

add_action( 'widgets_init', function() {
	unregister_sidebar( 'footer' );
	register_sidebar(
		[
			'name'          => __( 'Footer', 'octothorpe' ),
			'id'            => 'footer',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		]
	);
}, 11 );

add_filter( 'widget_links_args', function( $widget_links_args, $instance ) {
	$description = term_description( $widget_links_args['category'], 'link_category' );
	$widget_links_args['title_after'] .= $description;

	return $widget_links_args;
}, 10, 2 );

function atom_links() {
    $tmpl = '<link rel="%s" type="%s" title="%s" href="%s" />';

    printf(
        $tmpl,
        esc_attr( 'alternate' ),
        esc_attr( 'application/atom+xml' ),
        esc_attr( get_bloginfo( 'name' ) . '&raquo; Atom Feed link'  ),
		get_bloginfo( 'atom_url' )
    );
}
add_action( 'wp_head', __NAMESPACE__ . '\atom_links' );

function favicon() {
?>
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📔</text></svg>">
<?php
}
add_action( 'wp_head', __NAMESPACE__ . '\favicon' );
add_action( 'admin_head', __NAMESPACE__ . '\favicon' );

function add_atom_mime_support( $mimes ) {
	$mimes = array_merge(
		$mimes,
		array(
			'atom' => 'application/atom+xml',
		)
	);

	return $mimes;
}
add_filter( 'mime_types', __NAMESPACE__ . '\add_atom_mime_support' );
