<?php
/**
 * Suportes do tema, menus e tamanhos de imagem.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'contravencao', CV_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ] );
	add_theme_support( 'custom-logo', [ 'height' => 80, 'width' => 400, 'flex-width' => true, 'flex-height' => true ] );
	add_theme_support( 'responsive-embeds' );

	// WooCommerce: miniatura 5:7 (= 3:4.2 do protótipo).
	add_theme_support( 'woocommerce', [
		'thumbnail_image_width' => 600,
		'single_image_width'    => 900,
		'product_grid'          => [ 'default_columns' => 4, 'default_rows' => 3, 'min_columns' => 2, 'max_columns' => 4 ],
	] );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	add_image_size( 'cv-post', 640, 520, true );
	add_image_size( 'cv-portrait', 800, 1050, true );

	register_nav_menus( [
		'primary'      => 'Menu principal',
		'topbar-left'  => 'Topbar — esquerda',
		'topbar-right' => 'Topbar — direita',
		'footer-1'     => 'Rodapé — coluna 1',
		'footer-2'     => 'Rodapé — coluna 2',
		'footer-3'     => 'Rodapé — coluna 3',
	] );
} );

// Na ativação do tema: recorte da miniatura de produto em 5:7 (= 3:4.2 do protótipo).
// Depois pode ser alterado em Aparência > Personalizar > WooCommerce > Imagens do produto.
add_action( 'after_switch_theme', function () {
	update_option( 'woocommerce_thumbnail_cropping', 'custom' );
	update_option( 'woocommerce_thumbnail_cropping_custom_width', 5 );
	update_option( 'woocommerce_thumbnail_cropping_custom_height', 7 );
} );

// Sem margem do admin bar empurrando o <html>: o header fixo trata isso no CSS.
add_action( 'get_header', function () {
	remove_action( 'wp_head', '_admin_bar_bump_cb' );
} );

// Classe no body quando o header deve ficar transparente sobre o hero.
add_filter( 'body_class', function ( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'has-hero';
	}
	return $classes;
} );

// Remove emojis (peso extra desnecessário).
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// Resumo mais curto nos cards de blog.
add_filter( 'excerpt_length', fn() => 22 );
add_filter( 'excerpt_more', fn() => '…' );
