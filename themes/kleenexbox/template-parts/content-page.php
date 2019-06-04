<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header>
		<h2><?php the_title(); ?></h2>
	</header><!--End Header_page-->

	<?php the_content(); ?>

	<?php edit_post_link( 'Edit', '', '' ); ?>

</article>
