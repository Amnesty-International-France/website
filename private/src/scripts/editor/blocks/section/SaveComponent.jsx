import classnames from 'classnames';

const { useBlockProps, InnerBlocks } = wp.blockEditor;

const SaveComponent = ({ attributes }) => (
  <div
    {...useBlockProps.save()}
    className={classnames('section-block', attributes.sectionSize, attributes.backgroundColor, {
      'full-width': attributes.fullWidth,
    })}
  >
    <div className="section-block-content">
      {attributes.showTitle && <h3 className="section-block-content-title">{attributes.title}</h3>}
      <div className={classnames('section-block-inner-blocks-container', attributes.contentSize)}>
        <InnerBlocks.Content />
      </div>
    </div>
  </div>
);

export default SaveComponent;
