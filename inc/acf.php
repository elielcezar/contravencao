<?php
/**
 * ACF Pro: páginas de opções e campos (registrados em PHP, versionados com o tema).
 *
 * Contravenção > Home  → seções da home
 * Contravenção > Geral → topbar, rodapé
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page( [
		'page_title' => 'Contravenção',
		'menu_title' => 'Contravenção',
		'menu_slug'  => 'cv-options',
		'capability' => 'edit_theme_options',
		'icon_url'   => 'dashicons-store',
		'position'   => 59,
		'redirect'   => true,
	] );
	acf_add_options_sub_page( [ 'page_title' => 'Home', 'menu_title' => 'Home', 'menu_slug' => 'cv-home', 'parent_slug' => 'cv-options' ] );
	acf_add_options_sub_page( [ 'page_title' => 'Geral', 'menu_title' => 'Geral', 'menu_slug' => 'cv-general', 'parent_slug' => 'cv-options' ] );

	acf_add_local_field_group( cv_acf_home_group() );
	acf_add_local_field_group( cv_acf_general_group() );
} );

/* ---------- builders curtos ---------- */

function cv_f( string $type, string $name, string $label, array $extra = [] ): array {
	return array_merge( [ 'key' => 'field_cv_' . $name, 'name' => $name, 'label' => $label, 'type' => $type ], $extra );
}
function cv_tab( string $label ): array {
	return [ 'key' => 'field_cv_tab_' . sanitize_title( $label ), 'label' => $label, 'type' => 'tab', 'placement' => 'left' ];
}
function cv_img( string $name, string $label, array $extra = [] ): array {
	return cv_f( 'image', $name, $label, array_merge( [ 'return_format' => 'id', 'preview_size' => 'medium' ], $extra ) );
}

/** Vitrine de produtos com abas (repetidor de abas: rótulo + origem). */
function cv_acf_showcase( string $prefix, string $label ): array {
	$p = $prefix;
	return [
		cv_tab( $label ),
		cv_f( 'true_false', "{$p}_enabled", 'Exibir seção', [ 'ui' => 1, 'default_value' => 1 ] ),
		cv_f( 'text', "{$p}_title", 'Título' ),
		cv_f( 'number', "{$p}_limit", 'Produtos por aba', [ 'default_value' => 8, 'min' => 2, 'max' => 16 ] ),
		cv_f( 'repeater', "{$p}_tabs", 'Abas', [
			'layout'       => 'block',
			'button_label' => 'Adicionar aba',
			'min'          => 1,
			'max'          => 6,
			'sub_fields'   => [
				cv_f( 'text', "{$p}_tab_label", 'Rótulo', [ 'required' => 1, 'wrapper' => [ 'width' => 30 ] ] ),
				cv_f( 'select', "{$p}_tab_source", 'Origem', [
					'wrapper' => [ 'width' => 30 ],
					'choices' => [
						'category' => 'Categoria',
						'latest'   => 'Lançamentos',
						'popular'  => 'Mais vendidos',
						'sale'     => 'Em promoção',
						'featured' => 'Destaques (estrela)',
					],
					'default_value' => 'category',
				] ),
				cv_f( 'taxonomy', "{$p}_tab_cat", 'Categoria', [
					'wrapper'           => [ 'width' => 40 ],
					'taxonomy'          => 'product_cat',
					'field_type'        => 'select',
					'return_format'     => 'id',
					'add_term'          => 0,
					'conditional_logic' => [ [ [ 'field' => "field_cv_{$p}_tab_source", 'operator' => '==', 'value' => 'category' ] ] ],
				] ),
			],
		] ),
	];
}

function cv_acf_home_group(): array {
	$fields = [
		cv_tab( 'Hero' ),
		cv_f( 'repeater', 'hero_slides', 'Slides', [
			'layout'       => 'block',
			'button_label' => 'Adicionar slide',
			'collapsed'    => 'field_cv_hero_kicker',
			'sub_fields'   => [
				cv_img( 'hero_image', 'Imagem (1920×1080)', [ 'required' => 1, 'wrapper' => [ 'width' => 35 ] ] ),
				cv_f( 'text', 'hero_kicker', 'Chamada', [ 'wrapper' => [ 'width' => 65 ] ] ),
				cv_f( 'text', 'hero_title', 'Título', [ 'instructions' => 'Vazio = nome da loja.', 'wrapper' => [ 'width' => 65 ] ] ),
				cv_f( 'textarea', 'hero_text', 'Texto', [ 'rows' => 2 ] ),
				cv_f( 'link', 'hero_link', 'Botão' ),
			],
		] ),

		cv_tab( 'Faixas (marquee)' ),
		cv_f( 'text', 'marquee_1', 'Faixa 1 (após o hero)' ),
		cv_f( 'text', 'marquee_2', 'Faixa 2 (antes do blog)' ),
	];

	$fields = array_merge( $fields, cv_acf_showcase( 'grid', 'Vitrine 1 (grade)' ) );

	$fields = array_merge( $fields, [
		cv_tab( 'Categorias 2-up' ),
		cv_f( 'repeater', 'split_items', 'Blocos', [
			'layout'       => 'block',
			'min'          => 0,
			'max'          => 2,
			'button_label' => 'Adicionar bloco',
			'sub_fields'   => [
				cv_img( 'split_image', 'Imagem (1200×1400)', [ 'wrapper' => [ 'width' => 35 ] ] ),
				cv_f( 'text', 'split_title', 'Título', [ 'wrapper' => [ 'width' => 65 ] ] ),
				cv_f( 'textarea', 'split_text', 'Texto', [ 'rows' => 2 ] ),
				cv_f( 'link', 'split_link', 'Link' ),
			],
		] ),

		cv_tab( 'Frase' ),
		cv_f( 'textarea', 'statement', 'Frase de impacto', [ 'rows' => 3, 'new_lines' => 'br', 'instructions' => 'Use <em>palavra</em> para o sublinhado ondulado.' ] ),

		cv_tab( 'Banner parallax' ),
		cv_img( 'banner_image', 'Imagem (1920×1300)' ),
		cv_f( 'text', 'banner_title', 'Título' ),
		cv_f( 'textarea', 'banner_text', 'Texto', [ 'rows' => 2 ] ),
		cv_f( 'link', 'banner_link', 'Link (opcional)' ),
	] );

	$fields = array_merge( $fields, cv_acf_showcase( 'carousel', 'Vitrine 2 (carrossel)' ) );

	$fields = array_merge( $fields, [
		cv_tab( 'Depoimentos' ),
		cv_f( 'repeater', 'testimonials', 'Depoimentos', [
			'layout'       => 'block',
			'button_label' => 'Adicionar depoimento',
			'sub_fields'   => [
				cv_f( 'textarea', 'testimonial_quote', 'Depoimento', [ 'rows' => 3 ] ),
				cv_img( 'testimonial_avatar', 'Foto', [ 'wrapper' => [ 'width' => 35 ] ] ),
				cv_f( 'text', 'testimonial_name', 'Nome — cidade', [ 'wrapper' => [ 'width' => 65 ] ] ),
			],
		] ),

		cv_tab( 'Editorial' ),
		cv_f( 'textarea', 'editorial_title', 'Título', [ 'rows' => 3, 'new_lines' => 'br' ] ),
		cv_f( 'textarea', 'editorial_text', 'Texto', [ 'rows' => 2 ] ),
		cv_f( 'link', 'editorial_link', 'Botão' ),
		cv_img( 'editorial_image_main', 'Imagem principal (800×1050)', [ 'wrapper' => [ 'width' => 50 ] ] ),
		cv_img( 'editorial_image_side', 'Imagem lateral (600×640)', [ 'wrapper' => [ 'width' => 50 ] ] ),

		cv_tab( 'Blog' ),
		cv_f( 'true_false', 'blog_enabled', 'Exibir últimos posts', [ 'ui' => 1, 'default_value' => 1 ] ),
		cv_f( 'number', 'blog_count', 'Quantidade', [ 'default_value' => 4, 'min' => 2, 'max' => 8 ] ),
	] );

	return [
		'key'      => 'group_cv_home',
		'title'    => 'Home',
		'fields'   => $fields,
		'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'cv-home' ] ] ],
		'style'    => 'seamless',
	];
}

function cv_acf_general_group(): array {
	return [
		'key'      => 'group_cv_general',
		'title'    => 'Geral',
		'style'    => 'seamless',
		'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'cv-general' ] ] ],
		'fields'   => [
			cv_tab( 'Topbar' ),
			cv_f( 'text', 'topbar_notice', 'Aviso central', [ 'instructions' => 'Links da topbar: Aparência > Menus (locais "Topbar").' ] ),

			cv_tab( 'Rodapé' ),
			cv_f( 'text', 'footer_copyright', 'Copyright', [ 'instructions' => 'Use {ano} para o ano atual.' ] ),
			cv_f( 'text', 'footer_payments', 'Formas de pagamento', [ 'instructions' => 'Separadas por vírgula.' ] ),
		],
	];
}
