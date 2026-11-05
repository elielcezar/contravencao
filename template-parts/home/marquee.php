<?php
/**
 * Faixa de texto em movimento.
 *
 * @var array $args { field: string, reverse?: bool }
 */

defined( 'ABSPATH' ) || exit;

$text = cv_field( $args['field'] ?? 'marquee_1', '' );
if ( ! $text ) {
	return;
}
?>
<div class="marquee<?php echo ! empty( $args['reverse'] ) ? ' marquee--reverse' : ''; ?>" aria-hidden="true">
	<div class="marquee__track">
		<?php for ( $i = 0; $i < 4; $i++ ) : ?>
			<span><?php echo esc_html( $text ); ?></span><span class="dot">·</span>
		<?php endfor; ?>
	</div>
</div>
