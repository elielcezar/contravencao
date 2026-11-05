<?php
/**
 * Header global.
 */

defined( 'ABSPATH' ) || exit;

$has_woo     = class_exists( 'WooCommerce' );
$transparent = is_front_page();
$notice      = cv_field( 'topbar_notice', 'Frete grátis nas compras acima de R$ 299 · Troca fácil em até 30 dias' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link sr-only" href="#main">Pular para o conteúdo</a>

<header class="site-header<?php echo $transparent ? ' is-transparent' : ''; ?>" id="siteHeader">
	<div class="topbar">
		<div class="container topbar__inner">
			<nav class="topbar__links" aria-label="Links rápidos">
				<?php cv_menu( 'topbar-left', [ 'items_wrap' => '%3$s' ] ); ?>
			</nav>
			<p class="topbar__notice"><?php echo esc_html( $notice ); ?></p>
			<nav class="topbar__links topbar__links--right" aria-label="Atendimento">
				<?php cv_menu( 'topbar-right', [ 'items_wrap' => '%3$s' ] ); ?>
			</nav>
		</div>
	</div>

	<div class="header-main">
		<div class="container header-main__inner">
			<div class="header-main__left">
				<button class="icon-btn" type="button" data-open="menuDrawer" aria-label="Abrir menu" aria-controls="menuDrawer" aria-expanded="false">
					<?php echo cv_icon( 'menu' ); // phpcs:ignore ?>
				</button>
				<?php cv_logo(); ?>
			</div>

			<nav class="main-nav" aria-label="Menu principal">
				<?php
				if ( has_nav_menu( 'primary' ) ) {
					wp_nav_menu( [
						'theme_location' => 'primary',
						'container'      => false,
						'depth'          => 2,
						'cv_chevron'     => true,
					] );
				}
				?>
			</nav>

			<div class="header-main__right">
				<button class="icon-btn" type="button" data-open="searchPanel" aria-label="Buscar" aria-controls="searchPanel" aria-expanded="false">
					<?php echo cv_icon( 'search' ); // phpcs:ignore ?>
				</button>
				<?php if ( $has_woo ) : ?>
					<a class="icon-btn has-count" href="<?php echo esc_url( cv_wishlist_url() ); ?>" aria-label="Lista de desejos">
						<?php echo cv_icon( 'heart' ); // phpcs:ignore ?><span class="count cv-wish-count">0</span>
					</a>
					<a class="icon-btn hide-sm" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="Minha conta">
						<?php echo cv_icon( 'user' ); // phpcs:ignore ?>
					</a>
					<a class="icon-btn has-count" href="<?php echo esc_url( wc_get_cart_url() ); ?>" data-open="cartDrawer" aria-label="Carrinho" aria-controls="cartDrawer" aria-expanded="false">
						<?php echo cv_icon( 'bag' ); // phpcs:ignore ?><span class="count cv-cart-count"><?php echo (int) WC()->cart?->get_cart_contents_count(); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</header>
