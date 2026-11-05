<?php
/**
 * Dois blocos de categoria lado a lado.
 * Com $args['hero'] vira a abertura da home (sob o header transparente):
 * imagens sem lazy-load e com prioridade alta.
 */

defined( 'ABSPATH' ) || exit;

$items = cv_field( 'split_items', [] );
if ( ! $items ) {
	return;
}
$hero = ! empty( $args['hero'] );
?>
<section class="cat-split<?php echo $hero ? ' cat-split--hero' : ''; ?>">
	<?php foreach ( $items as $item ) : ?>
		<?php $link = cv_link( $item['split_link'] ?? null ); ?>
		<a href="<?php echo esc_url( $link['url'] ?: '#' ); ?>" target="<?php echo esc_attr( $link['target'] ); ?>" class="cat-split__item">
			<?php echo cv_image( $item['split_image'] ?? 0, 'large', array_merge(
				[ 'alt' => '', 'sizes' => '(max-width: 720px) 100vw, 50vw' ],
				$hero ? [ 'loading' => 'eager', 'fetchpriority' => 'high' ] : []
			) ); // phpcs:ignore ?>
			<div class="cat-split__caption">
				<h2><?php echo esc_html( $item['split_title'] ); ?></h2>
				<?php if ( ! empty( $item['split_text'] ) ) : ?>
					<p><?php echo esc_html( $item['split_text'] ); ?></p>
				<?php endif; ?>
			</div>
		</a>
	<?php endforeach; ?>
</section>
