<?php
/**
 * Dois blocos de categoria lado a lado.
 */

defined( 'ABSPATH' ) || exit;

$items = cv_field( 'split_items', [] );
if ( ! $items ) {
	return;
}
?>
<section class="cat-split">
	<?php foreach ( $items as $item ) : ?>
		<?php $link = cv_link( $item['split_link'] ?? null ); ?>
		<a href="<?php echo esc_url( $link['url'] ?: '#' ); ?>" target="<?php echo esc_attr( $link['target'] ); ?>" class="cat-split__item">
			<?php echo cv_image( $item['split_image'] ?? 0, 'large', [ 'alt' => '', 'sizes' => '(max-width: 720px) 100vw, 50vw' ] ); // phpcs:ignore ?>
			<div class="cat-split__caption">
				<h2><?php echo esc_html( $item['split_title'] ); ?></h2>
				<?php if ( ! empty( $item['split_text'] ) ) : ?>
					<p><?php echo esc_html( $item['split_text'] ); ?></p>
				<?php endif; ?>
			</div>
		</a>
	<?php endforeach; ?>
</section>
