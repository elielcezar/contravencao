<?php
/**
 * Funções utilitárias usadas pelos templates.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lê um campo ACF (página de opções por padrão) com valor padrão.
 * Se o ACF estiver desativado, devolve o padrão — o tema nunca quebra.
 */
function cv_field( string $name, $default = null, $post_id = 'option' ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$value = get_field( $name, $post_id );
	return ( $value === null || $value === '' || $value === false || $value === [] ) ? $default : $value;
}

/** Ícone do sprite SVG (template-parts/icons.php). */
function cv_icon( string $name, string $class = '' ): string {
	$class = $class ? ' class="' . esc_attr( $class ) . '"' : '';
	return '<svg' . $class . ' aria-hidden="true"><use href="#i-' . esc_attr( $name ) . '"/></svg>';
}

/** Imagem de anexo com fallback silencioso. */
function cv_image( $attachment_id, string $size = 'large', array $attr = [] ): string {
	if ( ! $attachment_id ) {
		return '';
	}
	return wp_get_attachment_image( (int) $attachment_id, $size, false, $attr );
}

/** Campo ACF do tipo "link" → [url, title, target]. */
function cv_link( $link, string $fallback_title = '' ): array {
	if ( is_array( $link ) && ! empty( $link['url'] ) ) {
		return [
			'url'    => $link['url'],
			'title'  => $link['title'] ?: $fallback_title,
			'target' => $link['target'] ?: '_self',
		];
	}
	return [ 'url' => '', 'title' => $fallback_title, 'target' => '_self' ];
}

/** Permite só <em>, <strong> e <br> em títulos editáveis. */
function cv_kses_inline( string $text ): string {
	return wp_kses( $text, [ 'em' => [], 'strong' => [], 'br' => [] ] );
}

/** Logo: logo personalizada do Customizer ou o nome do site em texto. */
function cv_logo( string $class = 'logo' ): void {
	$name = get_bloginfo( 'name' );
	if ( has_custom_logo() ) {
		$logo = wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'medium', false, [ 'alt' => $name ] );
		printf( '<a href="%s" class="%s logo--image" rel="home">%s</a>', esc_url( home_url( '/' ) ), esc_attr( $class ), $logo ); // phpcs:ignore
		return;
	}
	printf( '<a href="%s" class="%s" rel="home">%s</a>', esc_url( home_url( '/' ) ), esc_attr( $class ), esc_html( mb_strtoupper( $name ) ) );
}
