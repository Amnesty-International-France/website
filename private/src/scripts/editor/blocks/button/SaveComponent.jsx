import classnames from 'classnames';
import ArrowLeft from './icons/ArrowLeft.jsx';
import ArrowRight from './icons/ArrowRight.jsx';

const { useBlockProps } = wp.blockEditor;

const SaveComponent = ({ attributes }) => {
  const { label, size, style, icon, link, alignment } = attributes;

  return (
    <div className={classnames('button-container', alignment)}>
      <a
        {...useBlockProps.save()}
        href={link}
        target="_blank"
        rel="noopener noreferrer"
        className="custom-button"
      >
        <div className={classnames('content', size, style)}>
          {icon && (
            <div className="icon-container">
              {icon === 'arrow-left' && <ArrowLeft />}
              {icon === 'arrow-right' && <ArrowRight />}
            </div>
          )}
          <span>{label}</span>
        </div>
      </a>
    </div>
  );
};

export default SaveComponent;
