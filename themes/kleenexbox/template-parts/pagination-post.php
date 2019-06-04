<?php

wp_link_pages(
	[
		'before'           => '<nav role="navigation"><strong>Navigation:</strong><br/>',
		'after'            => '</nav>',
		'next_or_number'   => 'next',
		'nextpagelink'     => esc_html__( 'Next chapter', 'octothorpe' ),
		'previouspagelink' => esc_html__( 'Previous chapter', 'octothorpe' ),
		'separator'        => ' | ',
	]
);
