<?php
/**
 * Footer global + drawers, busca, toast e sprite de ícones.
 */

defined( 'ABSPATH' ) || exit;

$copyright = str_replace( '{ano}', wp_date( 'Y' ), cv_field( 'footer_copyright', '© {ano} Contravenção. Todos os direitos reservados.' ) );
$payments  = array_filter( array_map( 'trim', explode( ',', cv_field( 'footer_payments', 'Pix, Visa, Mastercard, Elo, Boleto' ) ) ) );
?>

<footer class="site-footer">
	<div class="container">
		<div class="footer-cols">
			<?php foreach ( [ 'footer-1', 'footer-2', 'footer-3' ] as $loc ) : ?>
				<?php if ( has_nav_menu( $loc ) ) : ?>
					<div class="footer-col">
						<h4><?php echo esc_html( cv_menu_title( $loc ) ); ?></h4>
						<?php cv_menu( $loc ); ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>

		<div class="footer-bottom">
			<p><?php echo esc_html( $copyright ); ?></p>
			<?php if ( $payments ) : ?>
				<ul class="payments" aria-label="Formas de pagamento">
					<?php foreach ( $payments as $pay ) : ?>
						<li><?php echo esc_html( $pay ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
	<div class="footer-wordmark" aria-hidden="true">
		<?php echo cv_lettering() ?: esc_html( mb_strtoupper( get_bloginfo( 'name' ) ) ); // phpcs:ignore ?>
	</div>
</footer>

<?php
get_template_part( 'template-parts/overlays' );
get_template_part( 'template-parts/icons' );
wp_footer();
?>
</body>
</html>
