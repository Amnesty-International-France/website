import classnames from 'classnames';

const { __ } = wp.i18n;
const { useBlockProps } = wp.blockEditor;

const SaveComponent = ({ attributes }) => (
  <div {...useBlockProps.save()} className="quote-block">
    {attributes.showImage && attributes.imageUrl && (
      <div className="quote-image">
        <img src={attributes.imageUrl} alt={__('Image de la citation', 'amnesty')} />
      </div>
    )}
    <div className={classnames('quote-content', attributes.bgColor)}>
      <blockquote className={classnames('text', attributes.size)}>
        {attributes.quoteText}
      </blockquote>
      <p className={classnames('author', attributes.size)}>{attributes.author}</p>
    </div>
  </div>
);

export default SaveComponent;
