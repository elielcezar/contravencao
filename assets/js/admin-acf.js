/**
 * Admin (ACF): trocar imagem/arquivo direto pela biblioteca de mídia.
 *
 * No ACF, com o campo preenchido, o lápis abre o modo "editar" do anexo (sem
 * upload). Aqui adicionamos um botão "Trocar" e o clique na própria imagem abre
 * a janela padrão do WordPress (enviar arquivo / biblioteca de mídia).
 */
(function ($) {
	if (typeof acf === 'undefined') return;

	const ICON = '<a class="acf-icon dark cv-acf-replace" data-name="replace" href="#" title="Trocar" aria-label="Trocar"><span class="dashicons dashicons-update"></span></a>';

	function setup(field) {
		if (typeof field.selectAttachment !== 'function') return;
		const $el = field.$el;
		const $actions = $el.find('.acf-actions.-hover').first();
		if (!$actions.length || $actions.find('.cv-acf-replace').length) return;

		$actions.prepend(ICON);

		const open = (e) => {
			e.preventDefault();
			e.stopPropagation();
			field.selectAttachment();
		};

		$actions.on('click', '.cv-acf-replace', open);

		// clique na imagem (fora dos ícones) também troca
		$el.find('.image-wrap > img').first().css('cursor', 'pointer').on('click', open);
	}

	acf.addAction('new_field/type=image', setup);
	acf.addAction('new_field/type=file', setup);
})(jQuery);
