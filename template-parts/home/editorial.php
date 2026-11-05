<?php
/**
 * Bloco editorial: texto + duas imagens.
 */

defined( 'ABSPATH' ) || exit;

$title = cv_field( 'editorial_title', '' );
if ( ! $title ) {
	return;
}
$link = cv_link( cv_field( 'editorial_link' ), 'Descobrir' );
?>
<section class="section editorial">
	<div class="container editorial__grid">
		<div class="editorial__text reveal">
			<h2><?php echo cv_kses_inline( $title ); // phpcs:ignore ?></h2>
			<?php if ( $text = cv_field( 'editorial_text' ) ) : ?>
				<p><?php echo esc_html( $text ); ?></p>
			<?php endif; ?>
			<?php if ( $link['url'] ) : ?>
				<a href="<?php echo esc_url( $link['url'] ); ?>" target="<?php echo esc_attr( $link['target'] ); ?>" class="btn btn--dark"><?php echo esc_html( $link['title'] ); ?></a>
			<?php endif; ?>
		</div>
		<figure class="editorial__main reveal">
			<?php echo cv_image( cv_field( 'editorial_image_main' ), 'cv-portrait', [ 'alt' => '', 'sizes' => '(max-width: 720px) 100vw, 35vw' ] ); // phpcs:ignore ?>
		</figure>
		<figure class="editorial__side reveal">
			<?php echo cv_image( cv_field( 'editorial_image_side' ), 'large', [ 'alt' => '', 'sizes' => '(max-width: 720px) 70vw, 30vw' ] ); // phpcs:ignore ?>
		</figure>
	</div>
</section>
