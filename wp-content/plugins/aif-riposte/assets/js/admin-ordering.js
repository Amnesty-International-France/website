(function ($) {
	'use strict';

	const $tableBody = $('#the-list');

	if (!$tableBody.length || typeof aifRiposteOrdering === 'undefined') {
		return;
	}

	$tableBody.sortable({
		items: 'tr',
		handle: '.aif-riposte-sort-handle',
		placeholder: 'aif-riposte-sort-placeholder',
		axis: 'y',
		update() {
			const order = [];

			$tableBody.find('tr').each(function () {
				const postId = $(this).attr('id');

				if (!postId) {
					return;
				}

				order.push(postId.replace('post-', ''));
			});

			$.post(aifRiposteOrdering.ajaxUrl, {
				action: 'aif_riposte_save_ordering',
				nonce: aifRiposteOrdering.nonce,
				order,
			});
		},
	});
})(jQuery);