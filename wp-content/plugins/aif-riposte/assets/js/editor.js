(function (wp) {
	'use strict';

	const { registerPlugin } = wp.plugins;
	const { PluginDocumentSettingPanel } = wp.editPost;
	const { TextControl } = wp.components;
	const { createElement: el } = wp.element;
	const { useSelect, useDispatch } = wp.data;

	function AifRiposteDatePanel() {
		const postType = useSelect((select) => {
			return select('core/editor').getCurrentPostType();
		}, []);

		const postId = useSelect((select) => {
			return select('core/editor').getCurrentPostId();
		}, []);

		const meta = useSelect((select) => {
			const record = select('core').getEditedEntityRecord('postType', postType, postId);

			return record && record.meta ? record.meta : {};
		}, [postType, postId]);

		const { editEntityRecord } = useDispatch('core');

		if ('riposte_victory' !== postType) {
			return null;
		}

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'aif-riposte-fields',
				title: 'Informations Riposte',
				className: 'aif-riposte-fields-panel',
			},
			el(
				'div',
				{
					style: {
						display: 'flex',
						flexDirection: 'column',
						gap: '16px',
					},
				},
				el(TextControl, {
					label: 'Date affichée sur la carte',
					type: 'date',
					value: meta.aif_riposte_date || '',
					onChange: function (value) {
						editEntityRecord('postType', postType, postId, {
							meta: {
								...meta,
								aif_riposte_date: value,
							},
						});
					},
				}),
				el(TextControl, {
					label: 'Lien externe',
					type: 'url',
					value: meta.aif_riposte_external_url || '',
					help: 'Lien optionnel',
					onChange: function (value) {
						editEntityRecord('postType', postType, postId, {
							meta: {
								...meta,
								aif_riposte_external_url: value,
							},
						});
					},
				})
			)
		);
	}

	registerPlugin('aif-riposte-date', {
		render: AifRiposteDatePanel,
	});
})(window.wp);