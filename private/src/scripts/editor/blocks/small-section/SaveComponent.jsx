const { useBlockProps, InnerBlocks } = wp.blockEditor;

const SaveComponent = ({ attributes }) => (
  <div {...useBlockProps.save()} className="small-section-block">
    <div className="small-section-block-content">
      {attributes.showTitle && (
        <h3 className="small-section-block-content-title">{attributes.title}</h3>
      )}
      <div className="small-section-block-inner-blocks-container">
        <InnerBlocks.Content />
      </div>
    </div>
  </div>
);

export default SaveComponent;
