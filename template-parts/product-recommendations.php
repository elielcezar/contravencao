<?php
/**
 * Seção "Você também pode gostar": título à esquerda + 4 produtos na largura toda,
 * mesma estrutura das vitrines da home. Usada na página do produto (related.php)
 * e no carrinho (inc/woocommerce.php).
 *
 * @var array $args { products: WC_Product[], title?: string }
 */

defined( 'ABSPATH' ) || exit;

$products = $args['products'] ?? [];
if ( ! $products ) {
	return;
}
$title = $args['title'] ?? 'Você também pode gostar';
?>
<section class="related">
	<?php if ( $title ) : ?>
		<div class="section-head">
			<h2 class="section-title"><?php echo esc_html( $title ); ?></h2>
		</div>
	<?php endif; ?>
	<ul class="products columns-4">
		<?php cv_render_product_cards( $products ); ?>
	</ul>
</section>
