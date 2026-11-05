<?php
/**
 * CSS e JS do tema.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', function () {
	// Versão = data de modificação do arquivo: nunca serve CSS/JS velho do cache.
	$ver = fn( string $rel ) => (string) ( filemtime( CV_DIR . $rel ) ?: CV_VERSION );

	wp_enqueue_style(
		'cv-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Instrument+Sans:wght@400;500;600&display=swap',
		[],
		null
	);
	wp_enqueue_style( 'cv-main', CV_URI . '/assets/css/main.css', [ 'cv-fonts' ], $ver( '/assets/css/main.css' ) );

	$deps = [];
	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style( 'cv-woocommerce', CV_URI . '/assets/css/woocommerce.css', [ 'cv-main' ], $ver( '/assets/css/woocommerce.css' ) );
		// O Woo 8+ não carrega mais estes por padrão; o tema depende deles (add-to-cart AJAX + fragments).
		wp_enqueue_script( 'wc-add-to-cart' );
		wp_enqueue_script( 'wc-cart-fragments' );
		$deps = [ 'jquery', 'wc-add-to-cart', 'wc-cart-fragments' ];
	}

	wp_enqueue_script( 'cv-main', CV_URI . '/assets/js/main.js', $deps, $ver( '/assets/js/main.js' ), [ 'in_footer' => true, 'strategy' => 'defer' ] );
	wp_localize_script( 'cv-main', 'CV', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'cv_wishlist' ),
		'i18n'    => [
			'added'       => 'adicionado ao carrinho',
			'wishAdded'   => 'Adicionado à lista de desejos',
			'wishRemoved' => 'Removido da lista de desejos',
			'error'       => 'Não foi possível concluir. Tente de novo.',
		],
	] );
} );

add_filter( 'wp_resource_hints', function ( $urls, $type ) {
	if ( $type === 'preconnect' ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = [ 'href' => 'https://fonts.gstatic.com', 'crossorigin' ];
	}
	return $urls;
}, 10, 2 );
