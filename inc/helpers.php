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

/**
 * Campo true/false do ACF. Diferente de cv_field(), "desligado" (false) é
 * respeitado; o padrão só vale se o campo nunca foi salvo.
 */
function cv_enabled( string $name, bool $default = true, $post_id = 'option' ): bool {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$value = get_field( $name, $post_id );
	return $value === null ? $default : (bool) $value;
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

/**
 * Lettering da marca (assets/images/logo.svg) inline, em currentColor:
 * herda a cor do contexto (branco sobre o hero, preto no header fixo etc.).
 * logo.svg é a versão monocromática de contravencao-lettering.svg.
 */
function cv_lettering( string $class = '' ): string {
	static $svg = null;
	if ( $svg === null ) {
		$file = CV_DIR . '/assets/images/logo.svg';
		$svg  = is_readable( $file ) ? trim( (string) file_get_contents( $file ) ) : '';
	}
	if ( $svg === '' ) {
		return '';
	}
	$attrs = sprintf( '<svg class="%s" role="img" aria-label="%s" focusable="false"', esc_attr( trim( 'lettering ' . $class ) ), esc_attr( get_bloginfo( 'name' ) ) );
	return preg_replace( '/^<svg/', $attrs, $svg, 1 );
}

/** Logo: logo do Customizer (se definida) > lettering SVG > nome do site em texto. */
function cv_logo( string $class = 'logo' ): void {
	$name = get_bloginfo( 'name' );
	$home = esc_url( home_url( '/' ) );

	if ( has_custom_logo() ) {
		$logo = wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'medium', false, [ 'alt' => $name ] );
		printf( '<a href="%s" class="%s logo--image" rel="home">%s</a>', $home, esc_attr( $class ), $logo ); // phpcs:ignore
		return;
	}
	$lettering = cv_lettering();
	if ( $lettering ) {
		printf( '<a href="%s" class="%s logo--svg" rel="home">%s</a>', $home, esc_attr( $class ), $lettering ); // phpcs:ignore
		return;
	}
	printf( '<a href="%s" class="%s" rel="home">%s</a>', $home, esc_attr( $class ), esc_html( mb_strtoupper( $name ) ) );
}
