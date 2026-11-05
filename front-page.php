<?php
/**
 * Home. Cada seção é um template-part em template-parts/home/;
 * o conteúdo vem de Contravenção > Home (ACF) com padrões no código.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main">
	<?php
	get_template_part( 'template-parts/home/cat-split', null, [ 'hero' => true ] );
	get_template_part( 'template-parts/home/showcase', null, [ 'prefix' => 'grid', 'layout' => 'grid' ] );
	get_template_part( 'template-parts/home/marquee', null, [ 'field' => 'marquee_1' ] );
	get_template_part( 'template-parts/home/banner' );
	get_template_part( 'template-parts/home/statement' );
	get_template_part( 'template-parts/home/testimonials' );
	get_template_part( 'template-parts/home/editorial' );
	get_template_part( 'template-parts/home/marquee', null, [ 'field' => 'marquee_2', 'reverse' => true ] );
	get_template_part( 'template-parts/home/blog' );
	?>
</main>
<?php
get_footer();
