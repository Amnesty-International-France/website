import classnames from 'classnames';
import Icon from '../../components/Icon.jsx';
import CustomButton from '../button/Button.jsx';

const { useBlockProps } = wp.blockEditor;
const { __ } = wp.i18n;

const SaveComponent = (props) => {
  const { attributes } = props;
  const {
    title,
    text,
    bgColor,
    showImage,
    mediaUrl,
    mediaPosition,
    icons,
    displayButton,
    buttonLabel,
    buttonLink,
    buttonPosition,
  } = attributes;

  return (
    <div {...useBlockProps.save()} className={classnames('section-home', bgColor)}>
      <div
        className={classnames('section-inner', {
          [mediaPosition]: showImage && mediaUrl,
          'with-image': showImage && mediaUrl,
          'without-image': !showImage || !mediaUrl,
        })}
      >
        {showImage && mediaUrl && (
          <div className="section-media">
            <div className="section-media-image-wrapper">
              <img
                className="section-media-image"
                src={mediaUrl}
                alt={__('Image de la section', 'amnesty')}
              />
            </div>
          </div>
        )}
        <div className="section-content">
          <h2 className="title">{title}</h2>
          {text && <p className="text">{text}</p>}
          {icons.length > 0 && (
            <div className="icons">
              {icons.map((iconItem, index) => (
                <div key={index} className="icon-with-text">
                  {iconItem.icon && (
                    <div className="container-icon">
                      <Icon
                        name={iconItem.icon}
                        colorClass={bgColor === 'black' ? 'primary' : 'black'}
                      />
                    </div>
                  )}
                  {iconItem.text && (
                    <p className={classnames('icon-description', bgColor)}>{iconItem.text}</p>
                  )}
                </div>
              ))}
            </div>
          )}
          {displayButton && (
            <CustomButton
              label={buttonLabel}
              size="medium"
              link={buttonLink}
              alignment={buttonPosition}
              style="bg-yellow"
              icon="arrow-right"
            />
          )}
        </div>
      </div>
    </div>
  );
};

export default SaveComponent;
