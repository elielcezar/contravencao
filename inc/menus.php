<?php
/**
 * Ajustes no markup dos menus para bater com o protótipo,
 * sem walker próprio: só filtros.
 */

defined( 'ABSPATH' ) || exit;

// Menu principal: item com filhos recebe .has-sub e chevron.
add_filter( 'nav_menu_css_class', function ( $classes, $item, $args, $depth ) {
	if ( ( $args->theme_location ?? '' ) === 'primary' && in_array( 'menu-item-has-children', $classes, true ) ) {
		$classes[] = 'has-sub';
	}
	return $classes;
}, 10, 4 );

add_filter( 'nav_menu_item_title', function ( $title, $item, $args, $depth ) {
	if ( ! empty( $args->cv_chevron ) && $depth === 0 && in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		$title .= ' ' . cv_icon( 'chevron', 'i-chev' );
	}
	return $title;
}, 10, 4 );

/** Título da coluna do rodapé = nome do menu atribuído ao local. */
function cv_menu_title( string $location ): string {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return '';
	}
	$menu = wp_get_nav_menu_object( $locations[ $location ] );
	return $menu ? $menu->name : '';
}

/** Menu sem <div> nem fallback (fica vazio se não houver menu atribuído). */
function cv_menu( string $location, array $args = [] ): void {
	if ( ! has_nav_menu( $location ) ) {
		return;
	}
	wp_nav_menu( array_merge( [
		'theme_location' => $location,
		'container'      => false,
		'fallback_cb'    => false,
		'depth'          => 1,
	], $args ) );
}
