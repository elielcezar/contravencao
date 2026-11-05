<?php
/**
 * Overlay, drawer do menu, drawer do carrinho, painel de busca, toast e "voltar ao topo".
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="overlay" id="overlay" hidden></div>

<aside class="drawer drawer--left" id="menuDrawer" aria-label="Menu" aria-hidden="true">
	<div class="drawer__head">
		<?php cv_logo( 'logo logo--dark' ); ?>
		<button class="icon-btn" type="button" data-close aria-label="Fechar"><?php echo cv_icon( 'close' ); // phpcs:ignore ?></button>
	</div>
	<nav class="drawer__nav" aria-label="Menu">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( [ 'theme_location' => 'primary', 'container' => false, 'depth' => 2 ] );
		}
		?>
	</nav>
	<div class="drawer__extra">
		<?php cv_menu( 'topbar-left' ); ?>
	</div>
</aside>

<?php if ( class_exists( 'WooCommerce' ) ) : ?>
	<aside class="drawer drawer--right" id="cartDrawer" aria-label="Carrinho" aria-hidden="true">
		<div class="drawer__head">
			<strong>Carrinho</strong>
			<button class="icon-btn" type="button" data-close aria-label="Fechar"><?php echo cv_icon( 'close' ); // phpcs:ignore ?></button>
		</div>
		<?php get_template_part( 'template-parts/mini-cart' ); ?>
	</aside>
<?php endif; ?>

<div class="search-panel" id="searchPanel" role="dialog" aria-label="Buscar" aria-hidden="true">
	<div class="container">
		<form class="search-panel__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php echo cv_icon( 'search' ); // phpcs:ignore ?>
			<label class="sr-only" for="searchInput">Buscar produtos</label>
			<input id="searchInput" type="search" name="s" placeholder="O que você procura?" value="<?php echo esc_attr( get_search_query() ); ?>">
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<input type="hidden" name="post_type" value="product">
			<?php endif; ?>
			<button class="icon-btn" type="button" data-close aria-label="Fechar"><?php echo cv_icon( 'close' ); // phpcs:ignore ?></button>
		</form>
	</div>
</div>

<div class="toast" id="toast" role="status" aria-live="polite"></div>
<button class="to-top" id="toTop" type="button" aria-label="Voltar ao topo"><?php echo cv_icon( 'chevron' ); // phpcs:ignore ?></button>
