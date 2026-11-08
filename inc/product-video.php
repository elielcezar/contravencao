<?php
/**
 * Bloco "texto + vídeo" na página do produto (campos ACF no produto).
 *
 * Fica logo abaixo da galeria/resumo e antes das abas, em duas colunas:
 * texto à esquerda, vídeo vertical (9:16) à direita. O vídeo toca sem som e
 * em loop quando entra na tela; o botão (ou clique no vídeo) ativa o som —
 * comportamento em assets/js/main.js (.cv-video).
 * A galeria do produto mostra só fotos.
 */

defined( 'ABSPATH' ) || exit;

/* ---------- Campos ---------- */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group( [
		'key'        => 'group_cv_product_video',
		'title'      => 'Bloco texto + vídeo',
		'position'   => 'normal',
		'menu_order' => 0,
		'location'   => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'product' ] ] ],
		'fields'     => [
			cv_f( 'message', 'product_video_help', '', [
				'message' => 'Aparece abaixo das fotos e do resumo do produto, antes das abas. Preencha o vídeo para exibir o bloco.',
			] ),
			cv_f( 'text', 'product_video_title', 'Título', [ 'wrapper' => [ 'width' => 60 ] ] ),
			cv_f( 'wysiwyg', 'product_video_text', 'Texto', [
				'tabs'         => 'all',
				'toolbar'      => 'basic',
				'media_upload' => 0,
				'wrapper'      => [ 'width' => 60 ],
			] ),
			cv_f( 'file', 'product_video', 'Vídeo (MP4)', [
				'return_format' => 'id',
				'library'       => 'all',
				'mime_types'    => 'mp4,webm,mov',
				'instructions'  => 'Vertical (9:16), curto e leve. Toca sem som e em loop; o cliente pode ativar o som.',
				'wrapper'       => [ 'width' => 40 ],
			] ),
			cv_img( 'product_video_poster', 'Capa do vídeo (opcional)', [
				'instructions' => 'Mostrada enquanto o vídeo carrega.',
				'wrapper'      => [ 'width' => 40 ],
			] ),
		],
	] );
} );

/* ---------- Dados ---------- */

/** Dados do bloco, ou null se não houver vídeo. */
function cv_product_video( WC_Product $product ): ?array {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}
	$id       = $product->get_id();
	$video_id = (int) get_field( 'product_video', $id );
	$url      = $video_id ? wp_get_attachment_url( $video_id ) : '';
	if ( ! $url ) {
		return null;
	}
	return [
		'url'    => $url,
		'mime'   => get_post_mime_type( $video_id ) ?: 'video/mp4',
		'poster' => (int) get_field( 'product_video_poster', $id ),
		'title'  => (string) get_field( 'product_video_title', $id ),
		'text'   => (string) get_field( 'product_video_text', $id ),
	];
}

/* ---------- Saída ---------- */
// Prioridade 5 = antes das abas (10), dentro do div.product (ocupa a largura toda no grid).
add_action( 'woocommerce_after_single_product_summary', function () {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$v = cv_product_video( $product );
	if ( ! $v ) {
		return;
	}
	$poster   = $v['poster'] ? wp_get_attachment_image_url( $v['poster'], 'woocommerce_single' ) : '';
	$has_text = trim( wp_strip_all_tags( $v['text'] ) ) !== '' || $v['title'] !== '';
	?>
	<section class="product-story<?php echo $has_text ? '' : ' product-story--solo'; ?>">
		<?php if ( $has_text ) : ?>
			<div class="product-story__text">
				<?php if ( $v['title'] ) : ?>
					<h2 class="product-story__title"><?php echo esc_html( $v['title'] ); ?></h2>
				<?php endif; ?>
				<div class="product-story__body"><?php echo wp_kses_post( $v['text'] ); ?></div>
			</div>
		<?php endif; ?>
		<div class="product-story__media">
			<div class="cv-video">
				<video muted loop playsinline preload="metadata"<?php echo $poster ? ' poster="' . esc_url( $poster ) . '"' : ''; ?>
					aria-label="<?php echo esc_attr( sprintf( 'Vídeo: %s', $product->get_name() ) ); ?>">
					<source src="<?php echo esc_url( $v['url'] ); ?>" type="<?php echo esc_attr( $v['mime'] ); ?>">
				</video>
				<button type="button" class="cv-video__sound" aria-pressed="false" aria-label="Ativar som">
					<?php echo cv_icon( 'sound-off', 'i-sound-off' ) . cv_icon( 'sound-on', 'i-sound-on' ); // phpcs:ignore ?>
				</button>
			</div>
		</div>
	</section>
	<?php
}, 5 );
