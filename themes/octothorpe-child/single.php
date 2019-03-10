<?php
/**
 * The post template
 *
 * @package Octothorpe
 * @subpackage Templates
 */

get_header();
?>
<p><a href="<?php echo home_url( '/' ); ?>">Home</a></p>
<?php
while ( have_posts() ) : the_post(); ?>
	<main role="main">
		<article <?php post_class(); ?>>
			<?php if ( has_category() ) { ?>
				<p><?php echo get_the_category_list( ', ' ); ?></p>
			<?php } ?>
			<?php the_title( '<header><h1>', '</h1></header>' ); ?>
			<?php
			$status = get_post_meta( get_the_ID(), '_cwr_docstate', true );

			if ( $status ) { ?>
				<p>Document status: <strong><?php echo ucfirst( $status ); ?></strong></p>
			<?php }
			the_content();
			get_template_part( 'template-parts/pag', 'post' );
			if ( has_tag() ) { ?>
				<p><?php esc_html_e( 'Tagged', 'octothorpe' ); ?> <?php echo get_the_tag_list( '', ', ', '' ); ?></p>
			<?php } ?>
			<address><?php printf(
				esc_html__( 'Posted by %1$s on %2$s.', 'octothorpe' ),
				get_the_author_posts_link(),
				sprintf(
					'<time datetime="%1$s">%2$s</time>',
					esc_attr( get_the_date( 'c' ) ),
					esc_html( get_the_date() )
				)
			); ?></address>
			<?php if ( comments_open() || get_comments_number() ) {
				comments_template();
			} ?>
		</article>
	</main>
<?php endwhile;
get_footer();
