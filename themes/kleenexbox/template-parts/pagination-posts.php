<?php

echo '<h4>Pagination:</h4>';

the_posts_navigation(
	array(
		'prev_text' => __( 'Previous', 'octothorpe' ),
		'next_text' => __( 'Next', 'octothorpe' )
	)
);
