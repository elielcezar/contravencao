<?php
/**
 * Banner com parallax.
 */

defined( 'ABSPATH' ) || exit;

$image = cv_field( 'banner_image', 0 );
if ( ! $image ) {
	return;
}
$link  = cv_link( cv_field( 'banner_link' ) );
$tag   = $link['url'] ? 'a' : 'section';
$attrs = $link['url'] ? sprintf( ' href="%s" target="%s"', esc_url( $link['url'] ), esc_attr( $link['target'] ) ) : '';
?>
<<?php echo $tag . $attrs; // phpcs:ignore ?> class="banner">
	<?php echo cv_image( $image, 'full', [ 'class' => 'banner__img', 'data-parallax' => '', 'alt' => '', 'sizes' => '100vw' ] ); // phpcs:ignore ?>
	<div class="banner__caption">
		<?php if ( $title = cv_field( 'banner_title' ) ) : ?>
			<h2><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>
		<?php if ( $text = cv_field( 'banner_text' ) ) : ?>
			<p><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>
	</div>
</<?php echo $tag; ?>>
