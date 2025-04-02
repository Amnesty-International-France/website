import classnames from 'classnames';
import Campaign from './Campaign.jsx';
import Petition from './Petition.jsx';
import CustomButton from '../button/Button.jsx';

const { useBlockProps } = wp.blockEditor;

const SaveComponent = ({ attributes }) => {
  const { type, title, subtitle, imageUrl, bgColor, buttonLink, buttonAlignment } = attributes;

  return (
    <div {...useBlockProps.save()} className={classnames('action', type)}>
      <div className="header">
        <p className="title">{title}</p>
        <div className="container-image">
          <img className="action-image" src={imageUrl} />
        </div>
        <div className="subtitle-container">
          <p className="subtitle">{subtitle}</p>
        </div>
      </div>
      <div className={classnames('content', bgColor)}>
        {type === 'petition' && (
          <Petition
            progress={70}
            button={
              <CustomButton
                label={'Signer la petition'}
                size={'medium'}
                icon={'pencil'}
                link={buttonLink}
                alignment={buttonAlignment}
              />
            }
          />
        )}
        {type === 'campaign' && (
          <Campaign
            button={
              <CustomButton
                label={'Label campagne'}
                size={'medium'}
                icon={'arrow-right'}
                link={buttonLink}
                alignment={buttonAlignment}
              />
            }
          />
        )}
      </div>
    </div>
  );
};

export default SaveComponent;
