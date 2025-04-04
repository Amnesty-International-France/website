const { __ } = wp.i18n;
const { useBlockProps, RichText } = wp.blockEditor;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const { text } = attributes;

  return (
    <div {...useBlockProps()} className="chapo">
      <RichText
        tagName="p"
        className="text"
        value={text}
        onChange={(value) => setAttributes({ text: value })}
        placeholder={__('Saisissez le texte du chapo…', 'amnesty')}
      />
    </div>
  );
};

export default EditComponent;
