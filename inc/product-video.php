<?php
/**
 * Vídeo do produto na galeria (1 por produto, campos ACF no produto).
 *
 * O slide segue as convenções que o JS da galeria do Woo 11 já entende:
 * - video.wp-post-video  → o Woo toca só o slide ativo e pausa os outros;
 * - data-thumb-video-src → sem capa, a miniatura vira um frame do vídeo;
 * - video[data-large_image] → o vídeo também aparece no lightbox (e os
 *   índices das fotos no lightbox não se desalinham).
 * O botão de som e um fallback de play/pause ficam em assets/js/main.js.
 */

defined( 'ABSPATH' ) || exit;

/* ---------- Campos ---------- */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group( [
		'key'      => 'group_cv_product_video',
		'title'    => 'Vídeo do produto',
		'position' => 'side',
		'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'product' ] ] ],
		'fields'   => [
			cv_f( 'file', 'product_video', 'Vídeo (MP4)', [
				'return_format' => 'id',
				'library'       => 'all',
				'mime_types'    => 'mp4,webm,mov',
				'instructions'  => 'Vertical (9:16), curto e leve. Toca sem som e em loop; o cliente pode ativar o som.',
			] ),
			cv_img( 'product_video_poster', 'Capa (opcional)', [
				'instructions' => 'Imagem da miniatura e do primeiro frame. Sem capa, usa um frame do próprio vídeo.',
			] ),
			cv_f( 'select', 'product_video_position', 'Posição na galeria', [
				'choices'       => [
					'first'  => 'Primeira',
					'second' => 'Depois da imagem principal',
					'last'   => 'Última',
				],
				'default_value' => 'first',
				'instructions'  => 'Em "Primeira", mantenha a foto de cada cor na galeria: ao escolher a cor, a galeria desliza até ela.',
			] ),
		],
	] );
} );

/* ---------- Dados ---------- */

/** Dados do vídeo do produto, ou null se não houver. */
function cv_product_video( WC_Product $product ): ?array {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}
	$video_id = (int) get_field( 'product_video', $product->get_id() );
	$url      = $video_id ? wp_get_attachment_url( $video_id ) : '';
	if ( ! $url ) {
		return null;
	}
	$meta = wp_get_attachment_metadata( $video_id ) ?: [];
	return [
		'id'       => $video_id,
		'url'      => $url,
		'mime'     => get_post_mime_type( $video_id ) ?: 'video/mp4',
		'width'    => (int) ( $meta['width'] ?? 1080 ),
		'height'   => (int) ( $meta['height'] ?? 1920 ),
		'poster'   => (int) get_field( 'product_video_poster', $product->get_id() ),
		'position' => get_field( 'product_video_position', $product->get_id() ) ?: 'first',
	];
}

/** Markup do slide de vídeo. */
function cv_product_video_slide( WC_Product $product, array $v ): string {
	$thumb  = $v['poster'] ? wp_get_attachment_image_url( $v['poster'], 'woocommerce_gallery_thumbnail' ) : '';
	$poster = $v['poster'] ? wp_get_attachment_image_url( $v['poster'], 'woocommerce_single' ) : '';
	$label  = sprintf( 'Vídeo: %s', $product->get_name() );
	// Sem capa: gif transparente no data-thumb; o JS do Woo sobrepõe um frame do vídeo.
	$blank = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

	return sprintf(
		'<div class="woocommerce-product-gallery__image cv-gallery-video" data-thumb="%1$s" data-thumb-alt="%2$s"%3$s>
			<div class="cv-video">
				<video class="wp-post-video" muted loop playsinline preload="metadata"%4$s
					data-large_image="%5$s" data-large_image_width="%6$d" data-large_image_height="%7$d" aria-label="%2$s">
					<source src="%5$s" type="%8$s">
				</video>
				<button type="button" class="cv-video__sound" aria-pressed="false" aria-label="Ativar som">%9$s%10$s</button>
			</div>
		</div>',
		esc_attr( $thumb ?: $blank ),
		esc_attr( $label ),
		$thumb ? '' : ' data-thumb-video-src="' . esc_url( $v['url'] ) . '"',
		$poster ? ' poster="' . esc_url( $poster ) . '"' : '',
		esc_url( $v['url'] ),
		$v['width'],
		$v['height'],
		esc_attr( $v['mime'] ),
		cv_icon( 'sound-off', 'i-sound-off' ),
		cv_icon( 'sound-on', 'i-sound-on' )
	);
}

/* ---------- Inserção na galeria ---------- */
// "Primeira": o slide entra antes da imagem principal (mesmo filtro que imprime a imagem principal).
add_filter( 'woocommerce_single_product_image_thumbnail_html', function ( $html, $attachment_id ) {
	static $done = [];
	global $product;
	if ( ! $product instanceof WC_Product || isset( $done[ $product->get_id() ] ) || (int) $attachment_id !== (int) $product->get_image_id() ) {
		return $html;
	}
	$done[ $product->get_id() ] = true;
	$v = cv_product_video( $product );
	return ( $v && $v['position'] === 'first' ) ? cv_product_video_slide( $product, $v ) . $html : $html;
}, 10, 2 );


// Prioridade 19 = antes das miniaturas da galeria (20), logo após a imagem principal; 30 = no fim.
foreach ( [ 'second' => 19, 'last' => 30 ] as $cv_pos => $cv_priority ) {
	add_action( 'woocommerce_product_thumbnails', function () use ( $cv_pos ) {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$v = cv_product_video( $product );
		if ( $v && $v['position'] === $cv_pos ) {
			echo cv_product_video_slide( $product, $v ); // phpcs:ignore
		}
	}, $cv_priority );
}

// Classe na galeria para o CSS saber que há vídeo.
add_filter( 'woocommerce_single_product_image_gallery_classes', function ( $classes ) {
	global $product;
	if ( $product instanceof WC_Product && cv_product_video( $product ) ) {
		$classes[] = 'has-video';
	}
	return $classes;
} );
