const { RichText, useBlockProps } = wp.blockEditor;

const SaveComponent = (props) => {
  const { attributes } = props;

  return (
    <div {...useBlockProps.save()} className="chapo">
      <RichText.Content tagName="p" className="text" value={attributes.text} />
    </div>
  );
};

export default SaveComponent;
