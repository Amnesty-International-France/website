import classnames from 'classnames';
import CustomButton from '../button/Button.jsx';

const { __ } = wp.i18n;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl, SelectControl } = wp.components;

const EditComponent = ({ attributes, setAttributes }) => {
  const { direction, title, subTitle, buttonLabel, buttonLink } = attributes;

  const updateDirection = (value) => {
    setAttributes({ direction: value });
  };

  const updateTitle = (newTitle) => {
    setAttributes({ title: newTitle });
  };

  const updateSubTitle = (newSubTitle) => {
    setAttributes({ subTitle: newSubTitle });
  };

  const updateButtonLabel = (newButtonLabel) => {
    setAttributes({ buttonLabel: newButtonLabel });
  };

  const updateButtonLink = (newButtonLink) => {
    setAttributes({ buttonLink: newButtonLink });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du bloc', 'amnesty')} initialOpen={true}>
          <SelectControl
            label={__('Disposition', 'amnesty')}
            value={direction}
            options={[
              { label: __('Horizontal', 'amnesty'), value: 'horizontal' },
              { label: __('Vertical', 'amnesty'), value: 'vertical' },
            ]}
            onChange={updateDirection}
          />
          <TextControl
            label={__('Titre', 'amnesty')}
            value={title}
            onChange={updateTitle}
            placeholder={__('Entrez un titre…', 'amnesty')}
          />
          <TextControl
            label={__('Sous-titre', 'amnesty')}
            value={subTitle}
            onChange={updateSubTitle}
            placeholder={__('Entrez un sous-titre…', 'amnesty')}
          />
          <TextControl
            label={__('Label du bouton', 'amnesty')}
            value={buttonLabel}
            onChange={updateButtonLabel}
            placeholder={__('Label du bouton', 'amnesty')}
          />
          <TextControl
            label={__('Lien du bouton', 'amnesty')}
            value={buttonLink}
            onChange={updateButtonLink}
            placeholder={__('Lien du bouton', 'amnesty')}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className={classnames('call-to-action-block', direction)}>
        <div className="call-to-action-content">
          <p className="title">{title}</p>
          <p className="subTitle">{subTitle}</p>
        </div>
        <CustomButton
          icon="arrow-right"
          label={buttonLabel}
          size="medium"
          link={buttonLink}
          style="bg-yellow"
        />
      </div>
    </>
  );
};

export default EditComponent;
