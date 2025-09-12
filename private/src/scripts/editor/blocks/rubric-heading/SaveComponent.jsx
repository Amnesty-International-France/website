const { RichText, useBlockProps } = wp.blockEditor;

const SaveComponent = (props) => {
  const { attributes } = props;
  const blockProps = useBlockProps.save({ className: 'rubric-heading' });
  const { kicker, heading } = attributes;

  return (
    <div {...blockProps}>
      <RichText.Content tagName="p" className="rubric-heading__kicker" value={kicker} />
      <RichText.Content tagName="h3" className="rubric-heading__heading" value={heading} />
    </div>
  );
};

export default SaveComponent;
