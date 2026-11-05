<?php
/**
 * Hero slider (Contravenção > Home > Hero).
 */

defined( 'ABSPATH' ) || exit;

$slides = cv_field( 'hero_slides', [] );
if ( ! $slides ) {
	return;
}
$brand = mb_strtoupper( get_bloginfo( 'name' ) );
?>
<section class="hero" aria-label="Destaques" aria-roledescription="carrossel">
	<div class="hero__slides">
		<?php foreach ( $slides as $i => $slide ) : ?>
			<?php
			$link  = cv_link( $slide['hero_link'] ?? null );
			$title = $slide['hero_title'] ?: $brand;
			$tag   = $i === 0 ? 'h1' : 'p';
			?>
			<div class="hero__slide<?php echo $i === 0 ? ' is-active' : ''; ?>" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( '%d de %d', $i + 1, count( $slides ) ) ); ?>">
				<?php
				echo cv_image( $slide['hero_image'] ?? 0, 'full', [ // phpcs:ignore
					'class'         => 'hero__img',
					'alt'           => '',
					'sizes'         => '100vw',
					'loading'       => $i === 0 ? 'eager' : 'lazy',
					'fetchpriority' => $i === 0 ? 'high' : 'auto',
				] );
				?>
				<div class="hero__content">
					<?php if ( ! empty( $slide['hero_kicker'] ) ) : ?>
						<p class="hero__kicker"><?php echo esc_html( $slide['hero_kicker'] ); ?></p>
					<?php endif; ?>
					<<?php echo $tag; ?> class="hero__title"><?php echo esc_html( $title ); ?></<?php echo $tag; ?>>
					<?php if ( ! empty( $slide['hero_text'] ) ) : ?>
						<p class="hero__text"><?php echo esc_html( $slide['hero_text'] ); ?></p>
					<?php endif; ?>
					<?php if ( $link['url'] ) : ?>
						<a href="<?php echo esc_url( $link['url'] ); ?>" target="<?php echo esc_attr( $link['target'] ); ?>" class="btn btn--light"><?php echo esc_html( $link['title'] ?: 'Ver coleção' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="hero__dots" aria-label="Slides"></div>
</section>
