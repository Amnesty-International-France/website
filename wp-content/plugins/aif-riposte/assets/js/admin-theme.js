(function (wp) {
	'use strict';

	if (!wp || !wp.data || !wp.domReady) {
		return;
	}

	wp.domReady(function () {
		const limitedTaxonomies = ['riposte_theme', 'riposte_tag', 'location'];
		const previousTerms = {};

		limitedTaxonomies.forEach(function (taxonomy) {
			previousTerms[taxonomy] = [];
		});

		wp.data.subscribe(function () {
			const editor = wp.data.select('core/editor');
			const dispatcher = wp.data.dispatch('core/editor');

			if (!editor || !dispatcher) {
				return;
			}

			if (editor.getCurrentPostType() !== 'riposte_victory') {
				return;
			}

			limitedTaxonomies.forEach(function (taxonomy) {
				const terms = editor.getEditedPostAttribute(taxonomy);

				if (!Array.isArray(terms)) {
					return;
				}

				if (terms.length <= 1) {
					previousTerms[taxonomy] = terms;
					return;
				}

				const lastSelected =
					terms.find(function (termId) {
						return !previousTerms[taxonomy].includes(termId);
					}) || terms[terms.length - 1];

				dispatcher.editPost({
					[taxonomy]: [lastSelected],
				});

				previousTerms[taxonomy] = [lastSelected];
			});
		});
	});
})(window.wp);