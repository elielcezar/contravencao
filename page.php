<?php
/**
 * Páginas (inclui carrinho, checkout e minha conta do Woo).
 * Páginas montadas no Elementor ocupam a largura toda, sem título do tema.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$is_elementor = did_action( 'elementor/loaded' )
	&& \Elementor\Plugin::$instance->documents->get( get_the_ID() )?->is_built_with_elementor();
$is_woo_page  = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
?>
<main id="main" class="site-main<?php echo $is_woo_page ? ' shop-main' : ''; ?>">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php if ( $is_elementor ) : ?>
			<?php the_content(); ?>
		<?php else : ?>
			<div class="container<?php echo $is_woo_page ? '' : ' container--narrow'; ?>">
				<header class="page-header">
					<h1 class="page-title"><?php the_title(); ?></h1>
				</header>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</div>
		<?php endif; ?>
	<?php endwhile; ?>
</main>
<?php
/** Seções de largura total entre o conteúdo e o footer (ex.: recomendações no carrinho). */
do_action( 'cv_after_main' );

get_footer();
