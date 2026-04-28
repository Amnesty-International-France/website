const { __ } = wp.i18n;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl, SelectControl, TextareaControl } = wp.components;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const { title, thematique, textHeader } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody>
          <SelectControl
            label={__('Choisir une thématique', 'amnesty')}
            value={thematique}
            options={[
              {
                label: __('Thématique'),
                value: '',
              },
              {
                label: __('Condamné(e)(s) peine de mort'),
                value: 'Condamné(e)(s) peine de mort',
              },
              {
                label: __('Défenseurs des droits humains'),
                value: 'Défenseurs des droits humains',
              },
              {
                label: __('Enseignants / étudiants'),
                value: 'Enseignants / étudiants',
              },
              {
                label: __('Femmes'),
                value: 'Femmes',
              },
              {
                label: __('Journalistes'),
                value: 'Journalistes',
              },
              {
                label: __('Justice climatique'),
                value: 'Justice climatique',
              },
              {
                label: __('Syndicalistes'),
                value: 'Syndicalistes',
              },
            ]}
            onChange={(value) => setAttributes({ thematique: value })}
          />
        </PanelBody>
        <PanelBody>
          <TextControl
            label={__('Titre', 'amnesty')}
            value={title}
            onChange={(value) => setAttributes({ title: value })}
          />
          <TextareaControl
            label={__('Description', 'amnesty')}
            value={textHeader}
            onChange={(value) => setAttributes({ textHeader: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div className="editor-preview-placeholder">
        <p>
          {__(
            'Veuillez sélectionner une thématique dans le menu de droite. Vous pouvez optionnellement ajouter un titre et une description.',
            'amnesty',
          )}
        </p>
        <p>{__('Les autres éléments du formulaires seront ajoutés automatiquement', 'amnesty')}</p>
        <div {...useBlockProps()}>
          <div className="header">
            <div className="title-wrapper">
              <p className="title">{title || __('Titre', 'amnesty')}</p>
              <p className="description">{textHeader || __('Description', 'amnesty')}</p>
            </div>
          </div>
        </div>
      </div>
    </>
  );
};
export default EditComponent;
