<?php
/**
 * The post pagination template part
 *
 * @package Octothorpe
 * @subpackage Templates
 */

wp_link_pages(
	[
		'before'           => '<nav role="navigation"><strong>Navigation:</strong><br/>',
		'after'            => '</nav>',
		'next_or_number'   => 'next',
		'nextpagelink'     => __( 'Next chapter', 'octothorpe' ),
		'previouspagelink' => __( 'Previous chapter', 'octothorpe' ),
		'separator'        => ' | ',
	]
);
