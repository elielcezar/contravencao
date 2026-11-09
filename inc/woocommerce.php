<?php
/**
 * Integração com o WooCommerce.
 */

defined( 'ABSPATH' ) || exit;

/* ---------- Estilos: o tema assume o visual (o CSS dos blocos de carrinho/checkout continua) ---------- */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/* ---------- Wrappers e sidebar ---------- */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

add_action( 'woocommerce_before_main_content', function () {
	echo '<main id="main" class="site-main shop-main"><div class="container">';
}, 10 );
add_action( 'woocommerce_after_main_content', function () {
	echo '</div></main>';
}, 10 );

/* ---------- Listagem ---------- */
add_filter( 'loop_shop_per_page', fn() => 12 );
add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
} );
add_filter( 'woocommerce_upsell_display_args', function ( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
} );

// Cabeçalho da listagem: título + contagem/ordenação numa barra.
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
add_action( 'woocommerce_before_shop_loop', function () {
	echo '<div class="shop-toolbar">';
	woocommerce_result_count();
	woocommerce_catalog_ordering();
	echo '</div>';
}, 20 );

// Breadcrumb no padrão do tema.
add_filter( 'woocommerce_breadcrumb_defaults', function ( $d ) {
	$d['delimiter']   = '<span class="sep">/</span>';
	$d['wrap_before'] = '<nav class="breadcrumb" aria-label="Breadcrumb">';
	$d['wrap_after']  = '</nav>';
	return $d;
} );

/* ---------- Helpers do card (woocommerce/content-product.php) ---------- */

/** Maior % de desconto do produto (simples ou variável). */
function cv_sale_percent( WC_Product $product ): int {
	if ( ! $product->is_on_sale() ) {
		return 0;
	}
	$pairs = [];
	if ( $product->is_type( 'variable' ) ) {
		foreach ( $product->get_visible_children() as $child_id ) {
			$child = wc_get_product( $child_id );
			if ( $child && $child->is_on_sale() ) {
				$pairs[] = [ (float) $child->get_regular_price(), (float) $child->get_sale_price() ];
			}
		}
	} else {
		$pairs[] = [ (float) $product->get_regular_price(), (float) $product->get_sale_price() ];
	}
	$max = 0;
	foreach ( $pairs as [ $regular, $sale ] ) {
		if ( $regular > 0 && $sale < $regular ) {
			$max = max( $max, (int) round( ( 1 - $sale / $regular ) * 100 ) );
		}
	}
	return $max;
}

/** ID da imagem de hover = primeira da galeria. */
function cv_hover_image_id( WC_Product $product ): int {
	$gallery = $product->get_gallery_image_ids();
	return $gallery ? (int) $gallery[0] : 0;
}

/** Estrelas (5) como no protótipo. */
function cv_stars( float $rating ): string {
	$out = '<span class="stars" aria-hidden="true">';
	for ( $i = 1; $i <= 5; $i++ ) {
		$out .= '<svg class="' . ( $i <= round( $rating ) ? 'on' : '' ) . '"><use href="#i-star"/></svg>';
	}
	return $out . '</span>';
}

/** Botão do card: AJAX para simples; link para variáveis/esgotados. */
function cv_card_button( WC_Product $product ): string {
	$name = $product->get_name();

	if ( $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) {
		return sprintf(
			'<a href="%s" data-quantity="1" data-product_id="%d" data-product_sku="%s" data-product_name="%s" class="product__add add_to_cart_button ajax_add_to_cart" aria-label="%s" rel="nofollow">%s</a>',
			esc_url( $product->add_to_cart_url() ),
			$product->get_id(),
			esc_attr( $product->get_sku() ),
			esc_attr( $name ),
			esc_attr( sprintf( 'Adicionar “%s” ao carrinho', $name ) ),
			'Adicionar ao carrinho'
		);
	}

	$label = ! $product->is_in_stock() ? 'Ver produto' : ( $product->is_type( 'variable' ) ? 'Escolher cor' : 'Ver opções' );
	return sprintf(
		'<a href="%s" class="product__add" aria-label="%s">%s</a>',
		esc_url( $product->get_permalink() ),
		esc_attr( $label . ': ' . $name ),
		esc_html( $label )
	);
}

/* ---------- Vitrines da home ---------- */

/** Busca produtos para uma aba da vitrine. */
function cv_showcase_products( string $source, $cat_id, int $limit ): array {
	$args = [ 'status' => 'publish', 'limit' => $limit, 'visibility' => 'catalog' ];

	switch ( $source ) {
		case 'latest':
			$args += [ 'orderby' => 'date', 'order' => 'DESC' ];
			break;
		case 'popular':
			$args += [ 'orderby' => 'popularity', 'order' => 'DESC' ];
			break;
		case 'sale':
			$ids = wc_get_product_ids_on_sale();
			if ( ! $ids ) {
				return [];
			}
			$args += [ 'include' => $ids, 'orderby' => 'date', 'order' => 'DESC' ];
			break;
		case 'featured':
			$args += [ 'featured' => true, 'orderby' => 'date', 'order' => 'DESC' ];
			break;
		default:
			$term = $cat_id ? get_term( (int) $cat_id, 'product_cat' ) : null;
			if ( ! $term || is_wp_error( $term ) ) {
				return [];
			}
			$args += [ 'category' => [ $term->slug ], 'orderby' => 'menu_order', 'order' => 'ASC' ];
	}
	return wc_get_products( $args );
}

/** Renderiza uma lista de produtos com o card do tema. */
function cv_render_product_cards( array $products ): void {
	global $product, $post;
	$prev_product = $product;
	$prev_post    = $post;

	foreach ( $products as $p ) {
		$post = get_post( $p->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		setup_postdata( $post );
		$product = $p; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		wc_get_template_part( 'content', 'product' );
	}

	$product = $prev_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	$post    = $prev_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	wp_reset_postdata();
}

/* ---------- Mini-carrinho (drawer) via fragments ---------- */
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$fragments['.cv-cart-count'] = '<span class="count cv-cart-count">' . (int) WC()->cart->get_cart_contents_count() . '</span>';

	ob_start();
	get_template_part( 'template-parts/mini-cart' );
	$fragments['#cv-mini-cart'] = ob_get_clean();

	return $fragments;
} );

/* ---------- Página do produto ---------- */
// Sem o bloco .product_meta (SKU / categoria / tags) na página do produto.
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

// Sem a aba "Informação adicional" (atributos / peso / dimensões).
add_filter( 'woocommerce_product_tabs', function ( $tabs ) {
	unset( $tabs['additional_information'] );
	return $tabs;
}, 98 );

/* Abaixo do preço: parcelamento sem juros e avaliações.
   As regras (parcelas, parcela mínima) ficam em Contravenção > Geral > Pagamento;
   em produtos variáveis o JS recalcula com o preço da cor escolhida (data-*). */
function cv_payment_rules(): array {
	return [
		'max' => max( 1, (int) cv_field( 'pay_installments', 6 ) ),
		'min' => max( 0, (float) cv_field( 'pay_min_installment', 0 ) ),
	];
}

/** Número de parcelas para um valor, respeitando a parcela mínima. */
function cv_installments_for( float $price, array $rules ): int {
	$n = $rules['max'];
	if ( $rules['min'] > 0 ) {
		$n = min( $n, (int) floor( $price / $rules['min'] ) );
	}
	return max( 1, $n );
}

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
add_action( 'woocommerce_single_product_summary', function () {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$price = (float) wc_get_price_to_display( $product );
	$rules = cv_payment_rules();

	if ( $price > 0 ) {
		$n = cv_installments_for( $price, $rules );
		printf(
			'<div class="price-info" data-price="%s" data-installments="%d" data-min="%s">
				<p class="price-info__inst"%s>ou <span class="js-n">%d</span>x de <strong class="js-inst">%s</strong> sem juros</p>
			</div>',
			esc_attr( $price ),
			(int) $rules['max'],
			esc_attr( $rules['min'] ),
			$n > 1 ? '' : ' hidden',
			(int) $n,
			wp_kses_post( wc_price( $price / $n ) )
		);
	}

	$count = (int) $product->get_review_count();
	if ( $count > 0 && wc_review_ratings_enabled() ) {
		$avg = (float) $product->get_average_rating();
		printf(
			'<a href="#reviews" class="price-info__rating woocommerce-review-link" aria-label="%1$s">%2$s<span>%3$s (%4$s)</span></a>',
			esc_attr( sprintf( 'Nota %s de 5, %d avaliações', wc_format_localized_decimal( round( $avg, 1 ) ), $count ) ),
			cv_stars( $avg ), // phpcs:ignore
			esc_html( sprintf( '%s estrelas', wc_format_localized_decimal( round( $avg, 1 ) ) ) ),
			esc_html( sprintf( _n( '%d avaliação', '%d avaliações', $count ), $count ) )
		);
	} elseif ( function_exists( 'qpi_get_source_rating' ) && ( $src = qpi_get_source_rating( $product->get_id() ) ) ) {
		// Sem avaliações da loja: nota do anúncio de origem, identificada (plugin Importador Rápido).
		$avg   = (float) $src['average'];
		$n     = (int) $src['count'];
		$label = $n >= 1000
			? wc_format_localized_decimal( rtrim( rtrim( number_format( $n / 1000, 1, '.', '' ), '0' ), '.' ) ) . ' mil'
			: number_format_i18n( $n );
		printf(
			'<a href="#reviews" class="price-info__rating woocommerce-review-link" aria-label="%1$s">%2$s<span>%3$s · %4$s avaliações</span></a>',
			esc_attr( sprintf( 'Nota %s de 5 com %s avaliações na %s', wc_format_localized_decimal( $avg ), $label, $src['source'] ) ),
			cv_stars( $avg ), // phpcs:ignore
			esc_html( wc_format_localized_decimal( $avg ) ),
			esc_html( $label ),
			esc_html( $src['source'] )
		);
	}
}, 11 );

// Relacionados saem da coluna do produto e viram uma seção de largura total antes do footer.
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
add_action( 'woocommerce_after_main_content', function () {
	if ( ! is_product() ) {
		return;
	}
	global $product;
	if ( ! $product instanceof WC_Product ) {
		$product = wc_get_product( get_queried_object_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	}
	if ( ! $product ) {
		return;
	}
	ob_start();
	woocommerce_output_related_products();
	$html = trim( ob_get_clean() );
	if ( $html ) {
		echo '<section class="product-related-section"><div class="container">' . $html . '</div></section>'; // phpcs:ignore
	}
}, 20 ); // depois do fechamento do <main> (prioridade 10)

// Variável: em vez da faixa "R$ X – R$ Y", mostra preço promocional + original riscado
// quando todas as cores têm o mesmo preço cheio; senão "A partir de R$ X".
add_filter( 'woocommerce_variable_price_html', function ( $html, $product ) {
	$min     = (float) $product->get_variation_price( 'min', true );
	$max     = (float) $product->get_variation_price( 'max', true );
	$reg_min = (float) $product->get_variation_regular_price( 'min', true );
	$reg_max = (float) $product->get_variation_regular_price( 'max', true );

	if ( $product->is_on_sale() && $reg_min === $reg_max && $min < $reg_max ) {
		return wc_format_sale_price( wc_price( $reg_max ), wc_price( $min ) ) . $product->get_price_suffix();
	}
	if ( $min === $max ) {
		return wc_price( $min ) . $product->get_price_suffix();
	}
	return '<span class="price-from">A partir de</span> ' . wc_price( $min ) . $product->get_price_suffix();
}, 10, 2 );

/* ---------- Adicionar ao carrinho ---------- */

// Página do produto: depois de adicionar, vai direto para o carrinho (o aviso
// "adicionado ao carrinho" aparece lá). Os cards (home/loja) seguem por AJAX:
// o envio do formulário do produto é POST; o fallback sem JS dos cards é GET.
add_filter( 'woocommerce_add_to_cart_redirect', function ( $url ) {
	if ( ! wp_doing_ajax() && isset( $_POST['add-to-cart'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return wc_get_cart_url();
	}
	return $url;
} );

/* ---------- Carrinho: "Você também pode gostar" ---------- */

/**
 * Recomendações para o carrinho: vendas cruzadas dos itens (se cadastradas),
 * depois produtos relacionados (mesma categoria/tag), completando com os mais
 * vendidos. Nunca repete o que já está no carrinho.
 */
function cv_cart_recommendations( int $limit = 4 ): array {
	$cart = WC()->cart;
	if ( ! $cart || $cart->is_empty() ) {
		return [];
	}

	$in_cart = [];
	foreach ( $cart->get_cart() as $item ) {
		$in_cart[] = (int) $item['product_id']; // produto pai (no caso de variação)
	}
	$in_cart = array_unique( $in_cart );

	$ids = [];
	foreach ( $in_cart as $id ) {
		$p = wc_get_product( $id );
		if ( $p ) {
			$ids = array_merge( $ids, $p->get_cross_sell_ids() );
		}
	}
	foreach ( $in_cart as $id ) {
		$ids = array_merge( $ids, wc_get_related_products( $id, $limit * 2, $in_cart ) );
	}
	$ids = array_values( array_diff( array_unique( array_map( 'intval', $ids ) ), $in_cart ) );

	$products = $ids ? wc_get_products( [
		'include'    => $ids,
		'status'     => 'publish',
		'visibility' => 'catalog',
		'limit'      => $limit,
		'orderby'    => 'post__in',
	] ) : [];

	if ( count( $products ) < $limit ) {
		$exclude = array_merge( $in_cart, array_map( fn( $p ) => $p->get_id(), $products ) );
		$more    = wc_get_products( [
			'status'     => 'publish',
			'visibility' => 'catalog',
			'limit'      => $limit - count( $products ),
			'orderby'    => 'popularity',
			'exclude'    => $exclude,
		] );
		$products = array_merge( $products, $more );
	}

	return array_filter( $products, fn( $p ) => $p->is_visible() );
}

// Seção de largura total depois do carrinho (antes do footer). Carrinho vazio: o
// próprio bloco do Woo já mostra novidades, então não repete.
add_action( 'cv_after_main', function () {
	if ( ! is_cart() ) {
		return;
	}
	$products = cv_cart_recommendations( 4 );
	if ( ! $products ) {
		return;
	}
	echo '<section class="product-related-section"><div class="container">';
	get_template_part( 'template-parts/product-recommendations', null, [ 'products' => $products ] );
	echo '</div></section>';
} );

// Esconde a coleção de vendas cruzadas que vem dentro do bloco do carrinho
// (depende de vínculo manual e duplicaria a seção acima).
add_filter( 'render_block', function ( $html, $block ) {
	if ( ( $block['blockName'] ?? '' ) === 'woocommerce/product-collection'
		&& ( $block['attrs']['collection'] ?? '' ) === 'woocommerce/product-collection/cross-sells' ) {
		return '';
	}
	return $html;
}, 10, 2 );

// Botão de lista de desejos ao lado do "Comprar".
add_action( 'woocommerce_after_add_to_cart_button', function () {
	global $product;
	if ( $product instanceof WC_Product && function_exists( 'cv_wishlist_button' ) ) {
		echo cv_wishlist_button( $product->get_id(), 'single-wish' ); // phpcs:ignore
	}
} );
