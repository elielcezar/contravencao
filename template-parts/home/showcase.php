<?php
/**
 * Vitrine de produtos com abas — usada em "grade" e "carrossel".
 * Todas as abas são renderizadas no servidor (funciona com cache de página);
 * o JS só alterna qual painel aparece.
 *
 * @var array $args { prefix: 'grid'|'carousel', layout: 'grid'|'carousel' }
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$p        = $args['prefix'];
$carousel = ( $args['layout'] ?? 'grid' ) === 'carousel';

if ( ! cv_enabled( "{$p}_enabled" ) ) {
	return;
}

$title = cv_field( "{$p}_title", '' );
$limit = (int) cv_field( "{$p}_limit", 8 );
$tabs  = cv_field( "{$p}_tabs", [] );

// Monta os painéis e descarta abas sem produto.
$panels = [];
foreach ( $tabs as $tab ) {
	$products = cv_showcase_products( $tab[ "{$p}_tab_source" ] ?? 'category', $tab[ "{$p}_tab_cat" ] ?? 0, $limit );
	if ( $products ) {
		$panels[] = [ 'label' => $tab[ "{$p}_tab_label" ], 'products' => $products ];
	}
}
if ( ! $panels ) {
	return;
}

$uid = 'sc-' . $p;
?>
<section class="section<?php echo $carousel ? '' : ' section--tight'; ?>" data-showcase>
	<div class="container">
		<div class="section-head">
			<?php if ( $title ) : ?>
				<h2 class="section-title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>
			<?php if ( count( $panels ) > 1 ) : ?>
				<div class="tabs" role="tablist" aria-label="<?php echo esc_attr( $title ); ?>">
					<?php foreach ( $panels as $i => $panel ) : ?>
						<button class="tabs__btn<?php echo $i === 0 ? ' is-active' : ''; ?>" type="button" role="tab"
							id="<?php echo esc_attr( "{$uid}-tab-{$i}" ); ?>"
							aria-controls="<?php echo esc_attr( "{$uid}-panel-{$i}" ); ?>"
							aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
							tabindex="<?php echo $i === 0 ? '0' : '-1'; ?>"><?php echo esc_html( $panel['label'] ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="tab-panels"<?php echo $carousel ? ' data-carousel' : ''; ?>>
			<?php foreach ( $panels as $i => $panel ) : ?>
				<ul class="products<?php echo $carousel ? ' products--carousel carousel__track' : ''; ?>"
					id="<?php echo esc_attr( "{$uid}-panel-{$i}" ); ?>"
					role="tabpanel" aria-labelledby="<?php echo esc_attr( "{$uid}-tab-{$i}" ); ?>"
					<?php echo $i > 0 ? 'hidden' : ''; ?>>
					<?php cv_render_product_cards( $carousel ? $panel['products'] : array_slice( $panel['products'], 0, $limit ) ); ?>
				</ul>
			<?php endforeach; ?>
			<?php if ( $carousel ) : ?>
				<div class="carousel__dots"></div>
			<?php endif; ?>
		</div>
	</div>
</section>
