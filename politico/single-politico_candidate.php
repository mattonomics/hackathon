<?php
get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

		<?php
		// Start the loop.
		while ( have_posts() ) : the_post();
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            	<?php twentyfifteen_post_thumbnail(); ?>

            	<header class="entry-header">
            		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
            	</header><!-- .entry-header -->

            	<div class="entry-content">
            		<?php the_content(); ?>
            	</div><!-- .entry-content -->

            	<footer class="entry-footer">
            	    <p>
            	        <strong><?php esc_html_e( 'Political Party:', 'politico' ); ?></strong>
            	        <?php echo esc_html( get_post_meta( get_the_ID(), 'politico_candidate_party', true ) ); ?>
            	    </p>
            	    <p>
                        <strong><?php esc_html_e( 'Home State:', 'politico' ); ?></strong>
                        <?php echo esc_html( get_post_meta( get_the_ID(), 'politico_candidate_state', true ) ); ?>
                    </p>
            	</footer><!-- .entry-footer -->

            </article><!-- #post-## -->


			<?php

			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;
			// End the loop.

		endwhile;
		?>

	</main><!-- .site-main -->
</div><!-- .content-area -->

<?php get_footer(); ?>

