<?php
/**
 * Página não encontrada.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main">
	<div class="container container--narrow not-found">
		<p class="not-found__code">404</p>
		<h1 class="page-title">Essa página saiu do mapa.</h1>
		<p>O endereço pode ter mudado ou não existe mais.</p>
		<a class="btn btn--dark" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>">Ir para a loja</a>
	</div>
</main>
<?php
get_footer();
