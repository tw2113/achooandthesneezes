<?php

get_header(); ?>
<main role="main">
	<header><h1><?php post_type_archive_title(); ?></h1></header>
	
	<?php
	the_archive_description();
	if ( have_posts() ) : ?>
		<ul class="post-list">
			<?php while ( have_posts() ) : the_post(); ?>
				<li <?php post_class(); ?>>
					<?php the_title(
						sprintf(
							'<a href="%1$s">',
							esc_url( get_permalink() )
						),
						'</a>'
					); ?>
				</li>
			<?php endwhile; ?>
		</ul>
		<?php get_template_part( 'template-parts/pag', 'posts' );
	else : ?>
		<p><?php esc_html_e( 'No posts found.', 'octothorpe' ); ?></p>
	<?php endif; ?>
</main>
<?php get_footer();

