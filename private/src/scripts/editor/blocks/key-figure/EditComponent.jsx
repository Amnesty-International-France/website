const { __ } = wp.i18n;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl } = wp.components;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const { title, text } = attributes;

  const onTitleChange = (value) => {
    setAttributes({ title: value });
  };

  const onTextChange = (value) => {
    setAttributes({ text: value });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres', 'amnesty')}>
          <TextControl
            label={__('Titre', 'amnesty')}
            value={title}
            onChange={onTitleChange}
            placeholder={__('Saisissez un titre…', 'amnesty')}
          />
          <TextControl
            label={__('Texte', 'amnesty')}
            value={text}
            onChange={onTextChange}
            placeholder={__('Saisissez votre texte…', 'amnesty')}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className="key-figure">
        <p className="title">{title}</p>
        <p className="text">{text}</p>
      </div>
    </>
  );
};

export default EditComponent;
