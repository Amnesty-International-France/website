const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const { useBlockProps } = wp.blockEditor;

const EditComponent = () => {
  const blockProps = useBlockProps({ className: 'change-their-history-toc-editor' });

  return (
    <div {...blockProps}>
      <p>{__('Sommaire automatique (H2)', 'amnesty')}</p>
      <p>{__("Les titres H2 s'afficheront ici.", 'amnesty')}</p>
    </div>
  );
};

registerBlockType('amnesty-core/change-their-history-toc', {
  title: __('Sommaire Changez leur histoire', 'amnesty'),
  description: __('Liste automatiquement les titres H2 de la page.', 'amnesty'),
  category: 'amnesty-core',
  icon: 'list-view',
  edit: EditComponent,
  save: () => null,
});
