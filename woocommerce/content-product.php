<?php
/**
 * Card de produto (loja, categorias, busca, relacionados, home, lista de desejos).
 *
 * Override de woocommerce/templates/content-product.php
 *
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

$name     = $product->get_name();
$link     = $product->get_permalink();
$hover_id = cv_hover_image_id( $product );
$off      = cv_sale_percent( $product );
$rating   = (float) $product->get_average_rating();
$reviews  = (int) $product->get_review_count();
?>
<li <?php wc_product_class( '', $product ); ?>>
	<div class="product__media">
		<a href="<?php echo esc_url( $link ); ?>" aria-label="<?php echo esc_attr( $name ); ?>" tabindex="-1">
			<?php
			echo $product->get_image( 'woocommerce_thumbnail', [ 'alt' => $name ] ); // phpcs:ignore
			if ( $hover_id ) {
				echo wp_get_attachment_image( $hover_id, 'woocommerce_thumbnail', false, [ 'class' => 'img-hover', 'alt' => '' ] );
			}
			?>
		</a>

		<?php if ( ! $product->is_in_stock() ) : ?>
			<span class="product__badge product__badge--out">Esgotado</span>
		<?php elseif ( $off ) : ?>
			<span class="product__badge">-<?php echo (int) $off; ?>%</span>
		<?php endif; ?>

		<div class="product__actions">
			<?php echo cv_wishlist_button( $product->get_id() ); // phpcs:ignore ?>
		</div>

		<?php echo cv_card_button( $product ); // phpcs:ignore ?>
	</div>

	<h3 class="product__title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $name ); ?></a></h3>
	<div class="product__price"><?php echo $product->get_price_html(); // phpcs:ignore ?></div>

	<?php if ( $reviews > 0 && wc_review_ratings_enabled() ) : ?>
		<div class="product__rating">
			<?php echo cv_stars( $rating ); // phpcs:ignore ?>
			<span><?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?> (<?php echo (int) $reviews; ?>)</span>
			<span class="sr-only"><?php echo esc_html( sprintf( 'Avaliação %s de 5', number_format_i18n( $rating, 1 ) ) ); ?></span>
		</div>
	<?php endif; ?>
</li>
