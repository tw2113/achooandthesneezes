<?php get_header(); ?>
<div class="content" role="main">

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<?php previous_post_link( '%link', '' . _x( '&larr;', 'Previous post link', 'twentyten' ) . ' %title' ); ?>
				<?php next_post_link( '%link', '%title ' . _x( '&rarr;', 'Next post link', 'twentyten' ) . '' ); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				    <header>
				    <h1><?php the_title(); ?></h1>
				    </header>

				    <?php the_content(); ?>
				    <footer>
				    <?php wp_link_pages( array( 'before' => '' . 'Pages:', 'after' => '' ) ); ?>

					<?php edit_post_link( 'Edit', '', '' ); ?>
					<?php previous_post_link( '%link', '' . _x( '&larr;', 'Previous post link', 'twentyten' ) . ' %title' ); ?>
					<?php next_post_link( '%link', '%title ' . _x( '&rarr;', 'Next post link', 'twentyten' ) . '' ); ?>
					</footer>
                </article>
<?php endwhile; // end of the loop. ?>

<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>
