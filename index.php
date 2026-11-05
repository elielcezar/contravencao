<?php
/**
 * Fallback: blog, arquivos e busca de posts.
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_home() && ! is_front_page() ) {
	$title = get_the_title( (int) get_option( 'page_for_posts' ) );
} elseif ( is_search() ) {
	$title = sprintf( 'Resultados para “%s”', get_search_query() );
} elseif ( is_archive() ) {
	$title = get_the_archive_title();
} else {
	$title = 'Blog';
}
?>
<main id="main" class="site-main">
	<div class="container">
		<header class="page-header">
			<h1 class="page-title"><?php echo wp_kses_post( $title ); ?></h1>
			<?php the_archive_description( '<div class="page-intro">', '</div>' ); ?>
		</header>

		<?php if ( have_posts() ) : ?>
			<ul class="posts">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/post-card' );
				endwhile;
				?>
			</ul>
			<?php
			the_posts_pagination( [ 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→' ] );
		else :
			?>
			<p class="empty-state">Nada encontrado por aqui.</p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
