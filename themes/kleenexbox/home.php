<?php get_header(); ?>
<div class="content" role="main">
	<div class="main-content">
		<?php
		get_template_part( 'template-parts/content', 'index' );
		?>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>
