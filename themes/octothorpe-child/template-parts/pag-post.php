<?php
/**
 * The post pagination template part
 *
 * @package Octothorpe
 * @subpackage Templates
 */

wp_link_pages(
	array(
		'before'           => '<nav role="navigation">Chapter navigation:<br/>',
		'after'            => '</nav>',
		'next_or_number'   => 'next',
		'nextpagelink'     => __( 'Next', 'octothorpe' ),
		'previouspagelink' => __( 'Previous', 'octothorpe' )
	)
);
