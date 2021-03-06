<?php
/**
 * The archive template
 *
 * @package Octothorpe
 * @subpackage Templates
 */

get_header(); ?>
<main role="main">
	<?php the_archive_title( '<header><h1>', '</h1></header>' );
	the_archive_description();
	if ( have_posts() ) : ?>
		<ul class="post-list">
			<?php while ( have_posts() ) : the_post(); ?>
				<li <?php post_class(); ?>>

					<?php the_title(
						sprintf(
							'<h3><a href="%1$s">',
							esc_url( get_permalink() )
						),
						'</a></h3>'
					); ?>
                    <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				</li>
			<?php endwhile; ?>
		</ul>
		<?php get_template_part( 'template-parts/pag', 'posts' );
	else : ?>
		<p><?php esc_html_e( 'No posts found.', 'octothorpe' ); ?></p>
	<?php endif; ?>
</main>
<?php get_footer();
