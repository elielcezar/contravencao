<?php
/**
 * Related Products — override do tema.
 *
 * O markup fica em template-parts/product-recommendations.php (compartilhado com
 * o carrinho). A <section> não leva a classe "products" (no Woo ela leva, e
 * herdava a grade dos cards).
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     10.3.0
 */

defined( 'ABSPATH' ) || exit;

if ( $related_products ) {
	if ( function_exists( 'wp_increase_content_media_count' ) ) {
		$content_media_count = wp_increase_content_media_count( 0 );
		if ( $content_media_count < wp_omit_loading_attr_threshold() ) {
			wp_increase_content_media_count( wp_omit_loading_attr_threshold() - $content_media_count );
		}
	}

	get_template_part( 'template-parts/product-recommendations', null, [
		'products' => $related_products,
		'title'    => apply_filters( 'woocommerce_product_related_products_heading', 'Você também pode gostar' ),
	] );
}

wp_reset_postdata();
