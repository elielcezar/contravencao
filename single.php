<?php
/**
 * Post do blog.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'container container--narrow' ); ?>>
			<header class="page-header page-header--post">
				<?php $cats = get_the_category(); ?>
				<?php if ( $cats ) : ?>
					<a class="post__cat" href="<?php echo esc_url( get_category_link( $cats[0] ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a>
				<?php endif; ?>
				<h1 class="page-title"><?php the_title(); ?></h1>
				<p class="post__meta">por <?php the_author(); ?> · <?php echo esc_html( get_the_date() ); ?></p>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="entry-cover"><?php the_post_thumbnail( 'large' ); ?></figure>
			<?php endif; ?>
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
