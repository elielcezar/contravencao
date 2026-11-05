<?php
/**
 * Related Products — override do tema.
 *
 * Mesma estrutura das vitrines da home: .section-head com o título à
 * esquerda e a grade de 4 produtos (.products) ocupando a largura toda.
 * A <section> não leva a classe "products" (no Woo ela leva, e herdava a grade).
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     10.3.0
 */

defined( 'ABSPATH' ) || exit;

if ( $related_products ) :
	if ( function_exists( 'wp_increase_content_media_count' ) ) {
		$content_media_count = wp_increase_content_media_count( 0 );
		if ( $content_media_count < wp_omit_loading_attr_threshold() ) {
			wp_increase_content_media_count( wp_omit_loading_attr_threshold() - $content_media_count );
		}
	}

	$heading = apply_filters( 'woocommerce_product_related_products_heading', 'Você também pode gostar' );
	?>
	<section class="related">
		<?php if ( $heading ) : ?>
			<div class="section-head">
				<h2 class="section-title"><?php echo esc_html( $heading ); ?></h2>
			</div>
		<?php endif; ?>

		<ul class="products columns-4">
			<?php
			foreach ( $related_products as $related_product ) :
				$post_object = get_post( $related_product->get_id() );
				setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found
				wc_get_template_part( 'content', 'product' );
			endforeach;
			?>
		</ul>
	</section>
	<?php
endif;

wp_reset_postdata();
