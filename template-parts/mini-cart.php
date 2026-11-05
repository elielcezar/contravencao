<?php
/**
 * Conteúdo do drawer do carrinho. Também é o fragment `#cv-mini-cart`
 * devolvido pelo AJAX do Woo (ver inc/woocommerce.php).
 */

defined( 'ABSPATH' ) || exit;

$cart  = WC()->cart;
$items = $cart ? $cart->get_cart() : [];
?>
<div id="cv-mini-cart" class="mini-cart-wrap">
	<?php if ( $items ) : ?>
		<ul class="mini-cart">
			<?php
			foreach ( $items as $key => $item ) :
				$product = apply_filters( 'woocommerce_cart_item_product', $item['data'], $item, $key );
				if ( ! $product || ! $product->exists() || $item['quantity'] <= 0 ) {
					continue;
				}
				$link = $product->is_visible() ? $product->get_permalink( $item ) : '';
				?>
				<li class="woocommerce-mini-cart-item">
					<?php echo $product->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore ?>
					<div>
						<?php if ( $link ) : ?>
							<a href="<?php echo esc_url( $link ); ?>"><strong><?php echo esc_html( $product->get_name() ); ?></strong></a>
						<?php else : ?>
							<strong><?php echo esc_html( $product->get_name() ); ?></strong>
						<?php endif; ?>
						<?php echo wc_get_formatted_cart_item_data( $item ); // phpcs:ignore ?>
						<div class="qty"><?php echo (int) $item['quantity']; ?> × <?php echo $cart->get_product_price( $product ); // phpcs:ignore ?></div>
					</div>
					<a href="<?php echo esc_url( wc_get_cart_remove_url( $key ) ); ?>"
						class="remove remove_from_cart_button"
						aria-label="<?php echo esc_attr( sprintf( 'Remover %s do carrinho', $product->get_name() ) ); ?>"
						data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
						data-cart_item_key="<?php echo esc_attr( $key ); ?>"
						data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>">×</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="mini-cart__foot">
			<div class="mini-cart__total"><span>Subtotal</span><strong><?php echo $cart->get_cart_subtotal(); // phpcs:ignore ?></strong></div>
			<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="btn btn--dark btn--block">Finalizar compra</a>
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="btn btn--outline btn--block">Ver carrinho</a>
		</div>
	<?php else : ?>
		<div class="mini-cart__empty">
			<?php echo cv_icon( 'bag' ); // phpcs:ignore ?>
			<p>Seu carrinho está vazio.</p>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn--dark">Continuar comprando</a>
		</div>
	<?php endif; ?>
</div>
