<?php
/**
 * Footer global + drawers, busca, toast e sprite de ícones.
 */

defined( 'ABSPATH' ) || exit;

$news_title     = cv_field( 'news_title', 'Receba novidades' );
$news_text      = cv_field( 'news_text', 'Cadastre seu e-mail para receber lançamentos e drops exclusivos — e ganhe 10% na primeira compra.' );
$news_shortcode = cv_field( 'news_shortcode', '' );
$news_legal     = cv_field( 'news_legal', '' );
$copyright      = str_replace( '{ano}', wp_date( 'Y' ), cv_field( 'footer_copyright', '© {ano} Contravenção. Todos os direitos reservados.' ) );
$payments       = array_filter( array_map( 'trim', explode( ',', cv_field( 'footer_payments', 'Pix, Visa, Mastercard, Elo, Boleto' ) ) ) );
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

			<div class="footer-col footer-col--news">
				<h4><?php echo esc_html( $news_title ); ?></h4>
				<p><?php echo esc_html( $news_text ); ?></p>
				<?php if ( $news_shortcode ) : ?>
					<div class="newsletter-embed"><?php echo do_shortcode( $news_shortcode ); ?></div>
				<?php else : ?>
					<form class="newsletter" action="#" onsubmit="return false">
						<label class="sr-only" for="newsEmail">Seu e-mail</label>
						<input id="newsEmail" type="email" placeholder="Seu e-mail" autocomplete="email" required>
						<button type="submit">Cadastrar</button>
					</form>
				<?php endif; ?>
				<?php if ( $news_legal ) : ?>
					<small><?php echo wp_kses_post( $news_legal ); ?></small>
				<?php endif; ?>
			</div>
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
	<div class="footer-wordmark" aria-hidden="true"><?php echo esc_html( mb_strtoupper( get_bloginfo( 'name' ) ) ); ?></div>
</footer>

<?php
get_template_part( 'template-parts/overlays' );
get_template_part( 'template-parts/icons' );
wp_footer();
?>
</body>
</html>
