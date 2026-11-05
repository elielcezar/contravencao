<?php
/**
 * Depoimentos.
 */

defined( 'ABSPATH' ) || exit;

$items = cv_field( 'testimonials', [] );
if ( ! $items ) {
	return;
}
?>
<section class="testimonials" aria-label="Depoimentos">
	<div class="container testimonials__inner" data-testimonials>
		<?php if ( count( $items ) > 1 ) : ?>
			<button class="arrow arrow--prev" type="button" aria-label="Depoimento anterior"><?php echo cv_icon( 'chevron' ); // phpcs:ignore ?></button>
		<?php endif; ?>
		<div class="testimonials__viewport" aria-live="polite">
			<?php foreach ( $items as $i => $t ) : ?>
				<figure class="testimonial<?php echo $i === 0 ? ' is-active' : ''; ?>"<?php echo $i ? ' aria-hidden="true"' : ''; ?>>
					<blockquote><?php echo esc_html( $t['testimonial_quote'] ); ?></blockquote>
					<?php echo cv_image( $t['testimonial_avatar'] ?? 0, 'thumbnail', [ 'class' => 'testimonial__avatar', 'alt' => '' ] ); // phpcs:ignore ?>
					<figcaption><?php echo esc_html( $t['testimonial_name'] ); ?></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
		<?php if ( count( $items ) > 1 ) : ?>
			<button class="arrow arrow--next" type="button" aria-label="Próximo depoimento"><?php echo cv_icon( 'chevron' ); // phpcs:ignore ?></button>
		<?php endif; ?>
	</div>
</section>
