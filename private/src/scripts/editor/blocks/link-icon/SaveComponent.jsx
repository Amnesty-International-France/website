import classnames from 'classnames';
import CustomButton from '../button/Button.jsx';
import Icon from '../../components/Icon.jsx';

const { useBlockProps } = wp.blockEditor;

const SaveComponent = ({ attributes }) => {
  const { title, titleSize, description, icon, bgColor, buttonLink } = attributes;

  const iconColorClass = bgColor === 'black' ? 'primary' : 'black';

  return (
    <div {...useBlockProps.save()} className={classnames('link-icon-block', bgColor, titleSize)}>
      <p className={classnames('title', titleSize)}>{title}</p>
      <div className="container-icon">
        <Icon name={icon} colorClass={iconColorClass} className="icon" />
      </div>
      <p className="description">{description}</p>
      <CustomButton
        label="En savoir plus"
        size="medium"
        link={buttonLink}
        alignment="center"
        style="bg-yellow"
      />
    </div>
  );
};

export default SaveComponent;
