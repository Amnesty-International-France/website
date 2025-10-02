import classnames from 'classnames';
import CustomButton from '../button/Button.jsx';
import Icon from '../../components/Icon.jsx';
import IconPickerControl from '../../../admin/components/IconPickerControl.jsx';
import PostSearchControl from '../../components/PostSearchControl.jsx';

const { __ } = wp.i18n;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl, ToggleControl } = wp.components;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const {
    title,
    titleSize,
    description,
    icon,
    bgColor,
    buttonLink,
    selectedPostTitle,
    displayButton,
  } = attributes;

  const allowedTypesForThisBlock = [
    'post',
    'pages',
    'fiche_pays',
    'landmark',
    'local-structures',
    'petition',
    'press-release',
    'training',
    'document',
    'edh',
    'chronique',
    'tribe_events',
  ];

  const handlePostSelect = (post) => {
    if (post) {
      setAttributes({
        buttonLink: post.link,
        selectedPostTitle: post.title.rendered,
      });
    } else {
      setAttributes({
        buttonLink: '',
        selectedPostTitle: '',
      });
    }
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres', 'amnesty')}>
          <TextControl
            label={__('Titre', 'amnesty')}
            value={title}
            onChange={(value) => setAttributes({ title: value })}
          />
          <TextControl
            label={__('Description', 'amnesty')}
            value={description}
            onChange={(value) => setAttributes({ description: value })}
          />
          <IconPickerControl
            label={__('Icône', 'amnesty')}
            value={icon}
            onChange={(newIcon) => setAttributes({ icon: newIcon })}
          />
          <ToggleControl
            label={__('Afficher le bouton', 'amnesty')}
            checked={!!displayButton}
            onChange={() => setAttributes({ displayButton: !displayButton })}
          />
          {displayButton && (
            <>
              <PostSearchControl
                allowedTypes={allowedTypesForThisBlock}
                onPostSelect={handlePostSelect}
              />
              {selectedPostTitle && (
                <p style={{ fontStyle: 'italic', marginTop: '1rem' }}>
                  {__('Contenu sélectionné :', 'amnesty')} <strong>{selectedPostTitle}</strong>
                </p>
              )}
            </>
          )}
        </PanelBody>
        <PanelBody title={__('Styles', 'amnesty')}>
          <SelectControl
            label={__('Taille du titre', 'amnesty')}
            value={titleSize}
            options={[
              { label: __('Petit', 'amnesty'), value: 'small' },
              { label: __('Moyen', 'amnesty'), value: 'medium' },
              { label: __('Grand', 'amnesty'), value: 'large' },
            ]}
            onChange={(value) => setAttributes({ titleSize: value })}
          />
          <SelectControl
            label={__('Couleur de fond', 'amnesty')}
            value={bgColor}
            options={[
              { label: __('Blanc', 'amnesty'), value: 'white' },
              { label: __('Gris', 'amnesty'), value: 'grey' },
              { label: __('Noir', 'amnesty'), value: 'black' },
            ]}
            onChange={(value) => setAttributes({ bgColor: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className={classnames('link-icon-block', bgColor)}>
        <p className={classnames('title', titleSize)}>{title}</p>
        {icon && (
          <div className="container-icon">
            <Icon name={icon} colorClass={bgColor === 'black' ? 'primary' : 'black'} />
          </div>
        )}
        <p className="description">{description}</p>
        {displayButton && (
          <CustomButton
            label="En savoir plus"
            size="medium"
            link={buttonLink}
            alignment="center"
            style="bg-yellow"
          />
        )}
      </div>
    </>
  );
};

export default EditComponent;
