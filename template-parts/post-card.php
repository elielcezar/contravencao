<?php
/**
 * Card de post (home e listagem do blog). Usar dentro do loop.
 */

defined( 'ABSPATH' ) || exit;

$cats = get_the_category();
?>
<li class="post">
	<a href="<?php the_permalink(); ?>" class="post__thumb" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'cv-post', [ 'alt' => '' ] );
		}
		?>
	</a>
	<?php if ( $cats ) : ?>
		<span class="post__cat"><?php echo esc_html( $cats[0]->name ); ?></span>
	<?php endif; ?>
	<h3 class="post__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
	<span class="post__meta">por <?php the_author(); ?> · <?php echo esc_html( get_the_date( 'd M Y' ) ); ?></span>
</li>
