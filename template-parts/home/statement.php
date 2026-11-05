<?php
/**
 * Frase de impacto.
 */

defined( 'ABSPATH' ) || exit;

$text = cv_field( 'statement', '' );
if ( ! $text ) {
	return;
}
?>
<section class="statement">
	<div class="container">
		<p class="statement__text reveal"><?php echo cv_kses_inline( $text ); // phpcs:ignore ?></p>
	</div>
</section>
