<?php
/**
 * Últimos posts do blog.
 */

defined( 'ABSPATH' ) || exit;

if ( ! cv_enabled( 'blog_enabled' ) ) {
	return;
}

$query = new WP_Query( [
	'post_type'           => 'post',
	'posts_per_page'      => (int) cv_field( 'blog_count', 4 ),
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
] );

if ( ! $query->have_posts() ) {
	return;
}
?>
<section class="section section--tight">
	<div class="container">
		<ul class="posts">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				get_template_part( 'template-parts/post-card' );
			endwhile;
			wp_reset_postdata();
			?>
		</ul>
	</div>
</section>
