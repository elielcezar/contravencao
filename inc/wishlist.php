<?php
/**
 * Lista de desejos própria (sem plugin).
 *
 * - Visitante: cookie `cv_wishlist` (IDs separados por vírgula).
 * - Logado: user meta `cv_wishlist`; o cookie é espelhado para o JS ler o estado
 *   (os corações funcionam mesmo com cache de página).
 * - No login, o que estava no cookie é mesclado na conta.
 * - Página: qualquer página com o shortcode [cv_wishlist].
 */

defined( 'ABSPATH' ) || exit;

const CV_WL_KEY = 'cv_wishlist';
const CV_WL_MAX = 100;

function cv_wishlist_sanitize( $ids ): array {
	$ids = array_filter( array_map( 'absint', (array) $ids ) );
	return array_slice( array_values( array_unique( $ids ) ), 0, CV_WL_MAX );
}

function cv_wishlist_from_cookie(): array {
	$raw = isset( $_COOKIE[ CV_WL_KEY ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ CV_WL_KEY ] ) ) : '';
	return cv_wishlist_sanitize( $raw === '' ? [] : explode( ',', $raw ) );
}

function cv_wishlist_get( int $user_id = 0 ): array {
	$user_id = $user_id ?: get_current_user_id();
	if ( $user_id ) {
		return cv_wishlist_sanitize( get_user_meta( $user_id, CV_WL_KEY, true ) ?: [] );
	}
	return cv_wishlist_from_cookie();
}

function cv_wishlist_set_cookie( array $ids ): void {
	if ( headers_sent() ) {
		return;
	}
	$value  = implode( ',', $ids );
	$expiry = $value === '' ? time() - HOUR_IN_SECONDS : time() + YEAR_IN_SECONDS;
	setcookie( CV_WL_KEY, $value, [
		'expires'  => $expiry,
		'path'     => COOKIEPATH ?: '/',
		'domain'   => COOKIE_DOMAIN,
		'secure'   => is_ssl(),
		'httponly' => false, // o JS precisa ler
		'samesite' => 'Lax',
	] );
	$_COOKIE[ CV_WL_KEY ] = $value;
}

function cv_wishlist_save( array $ids, int $user_id = 0 ): array {
	$ids     = cv_wishlist_sanitize( $ids );
	$user_id = $user_id ?: get_current_user_id();
	if ( $user_id ) {
		update_user_meta( $user_id, CV_WL_KEY, $ids );
	}
	cv_wishlist_set_cookie( $ids );
	return $ids;
}

/* ---------- AJAX: alternar item ---------- */
function cv_wishlist_ajax_toggle(): void {
	check_ajax_referer( 'cv_wishlist', 'nonce' );

	$id      = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$product = $id ? wc_get_product( $id ) : null;
	if ( ! $product || $product->get_status() !== 'publish' ) {
		wp_send_json_error( [ 'message' => 'Produto inválido.' ], 400 );
	}

	$ids   = cv_wishlist_get();
	$added = ! in_array( $id, $ids, true );
	$ids   = $added ? array_merge( [ $id ], $ids ) : array_values( array_diff( $ids, [ $id ] ) );
	$ids   = cv_wishlist_save( $ids );

	wp_send_json_success( [ 'ids' => $ids, 'added' => $added, 'count' => count( $ids ) ] );
}
add_action( 'wp_ajax_cv_wishlist_toggle', 'cv_wishlist_ajax_toggle' );
add_action( 'wp_ajax_nopriv_cv_wishlist_toggle', 'cv_wishlist_ajax_toggle' );

/* ---------- Login/logout: mescla e espelha ---------- */
add_action( 'wp_login', function ( $login, $user ) {
	$merged = array_merge( cv_wishlist_get( $user->ID ), cv_wishlist_from_cookie() );
	cv_wishlist_save( $merged, $user->ID );
}, 10, 2 );

add_action( 'wp_logout', function () {
	cv_wishlist_set_cookie( [] );
} );

// Logado em outro aparelho: garante o cookie espelhado.
add_action( 'template_redirect', function () {
	if ( is_user_logged_in() ) {
		$ids = cv_wishlist_get();
		if ( $ids !== cv_wishlist_from_cookie() ) {
			cv_wishlist_set_cookie( $ids );
		}
	}
} );

/* ---------- Markup ---------- */

/** Botão de coração. O estado ativo é aplicado pelo JS a partir do cookie. */
function cv_wishlist_button( int $product_id, string $class = '' ): string {
	return sprintf(
		'<button type="button" class="%s" data-wish="%d" aria-pressed="false" aria-label="Adicionar à lista de desejos">%s</button>',
		esc_attr( trim( 'wish-btn ' . $class ) ),
		$product_id,
		cv_icon( 'heart' )
	);
}

/** URL da página da lista de desejos (a primeira com o shortcode). */
function cv_wishlist_url(): string {
	$page_id = (int) get_option( 'cv_wishlist_page_id' );
	if ( ! $page_id || get_post_status( $page_id ) !== 'publish' ) {
		global $wpdb;
		$page_id = (int) $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE post_type='page' AND post_status='publish' AND post_content LIKE '%[cv_wishlist%' LIMIT 1" );
		update_option( 'cv_wishlist_page_id', $page_id, true );
	}
	return $page_id ? get_permalink( $page_id ) : wc_get_page_permalink( 'shop' );
}

add_action( 'save_post_page', fn() => delete_option( 'cv_wishlist_page_id' ) );

add_shortcode( 'cv_wishlist', function () {
	$ids = cv_wishlist_get();
	ob_start();

	echo '<div class="wishlist" data-wishlist-page>';
	if ( $ids ) {
		$products = wc_get_products( [ 'include' => $ids, 'status' => 'publish', 'limit' => CV_WL_MAX, 'orderby' => 'post__in' ] );
		echo '<ul class="products columns-4">';
		cv_render_product_cards( $products );
		echo '</ul>';
	}
	printf(
		'<div class="wishlist__empty"%s><p>Sua lista de desejos está vazia.</p><a class="btn btn--dark" href="%s">Explorar a loja</a></div>',
		$ids ? ' hidden' : '',
		esc_url( wc_get_page_permalink( 'shop' ) )
	);
	echo '</div>';

	return ob_get_clean();
} );

// A página da lista depende do cookie: não pode ir para cache.
add_action( 'template_redirect', function () {
	if ( is_page() && has_shortcode( (string) get_post_field( 'post_content', get_queried_object_id() ), 'cv_wishlist' ) ) {
		nocache_headers();
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
	}
} );
