<?php get_header(); ?>
<div class="content" role="main">
	<div class="main-content">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<?php get_template_part( 'template-parts/content', 'frontpage' ); ?>
		<?php endwhile; endif; ?>

		<?php get_template_part( 'template-parts/content', 'pagination' ); ?>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>
