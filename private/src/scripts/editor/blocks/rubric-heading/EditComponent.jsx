const { __ } = wp.i18n;
const { useBlockProps, RichText } = wp.blockEditor;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const blockProps = useBlockProps({ className: 'rubric-heading' });
  const { kicker, heading } = attributes;

  return (
    <div {...blockProps}>
      <RichText
        tagName="p"
        className="rubric-heading__kicker"
        value={kicker}
        onChange={(newKicker) => setAttributes({ kicker: newKicker })}
        placeholder={__('Saisir la catégorie (ex: Dossier)…', 'amnesty')}
        withoutInteractiveFormatting
      />
      <RichText
        tagName="h3"
        className="rubric-heading__heading"
        value={heading}
        onChange={(newHeading) => setAttributes({ heading: newHeading })}
        placeholder={__('Saisir le titre…', 'amnesty')}
        withoutInteractiveFormatting
      />
    </div>
  );
};

export default EditComponent;
