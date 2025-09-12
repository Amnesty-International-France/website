const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl, TextareaControl } = wp.components;
const { __ } = wp.i18n;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const { title, text } = attributes;
  const blockProps = useBlockProps({ className: 'content-callout' });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Contenu de la mise en exergue', 'amnesty')}>
          <TextControl
            label={__('Titre', 'amnesty')}
            value={title}
            onChange={(newTitle) => setAttributes({ title: newTitle })}
          />
          <TextareaControl
            label={__('Texte', 'amnesty')}
            value={text}
            onChange={(newText) => setAttributes({ text: newText })}
            rows={5}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div className="container">
          <h3 className="content-callout__title">{title}</h3>
          <p className="content-callout__text" style={{ whiteSpace: 'pre-wrap' }}>
            {text}
          </p>
        </div>
      </div>
    </>
  );
};

export default EditComponent;
