import classnames from 'classnames';
import Icon from '../../components/Icon.jsx';
import CustomButton from '../button/Button.jsx';

const { useEffect, useState } = wp.element;
const { useBlockProps, InspectorControls, MediaUpload } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl, Button, TextareaControl, ToggleControl } =
  wp.components;
const { __ } = wp.i18n;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const {
    title,
    text,
    bgColor,
    showImage,
    mediaId,
    mediaUrl,
    mediaPosition,
    icons,
    displayButton,
    buttonLabel,
    buttonLink,
    buttonPosition,
  } = attributes;

  const onSelectImage = (media) => {
    setAttributes({
      mediaId: media.id,
      mediaUrl: media.url,
    });
  };

  const reqSvgs = require.context('../../icons', false, /\.svg$/);

  const iconOptions = reqSvgs.keys().map((filePath) => {
    const fileName = filePath.replace('./', '').replace('.svg', '');
    return {
      label: fileName,
      value: fileName,
    };
  });

  const addIcon = () => {
    setAttributes({
      icons: [...icons, { icon: '', text: '' }],
    });
  };

  const updateIcon = (index, key, value) => {
    const newIcons = [...icons];
    newIcons[index][key] = value;
    setAttributes({ icons: newIcons });
  };

  const removeIcon = (index) => {
    const newIcons = icons.filter((_, i) => i !== index);
    setAttributes({ icons: newIcons });
  };

  const renderIconBlock = (iconItem) => {
    if (!iconItem.icon) {
      return null;
    }
    return (
      <div className="icon-with-text">
        <div className="container-icon">
          <Icon name={iconItem.icon} colorClass={bgColor === 'black' ? 'primary' : 'black'} />
        </div>
        {iconItem.text && (
          <p className={classnames('icon-description', bgColor)}>{iconItem.text}</p>
        )}
      </div>
    );
  };

  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/wp-json/wp/v2/posts')
      .then((response) => response.json())
      .then((data) => {
        setPosts(data);
        setLoading(false);
      })
      .catch((error) => {
        console.error('Erreur de récupération des posts', error);
        setLoading(false);
      });
  }, []);

  const handlePostSelect = (selectedLink) => {
    setAttributes({
      buttonLink: selectedLink,
    });
  };

  const selectedPost = posts.find((post) => post.link === buttonLink);

  const postOptions = [
    { label: __('Choisir un post', 'amnesty'), value: '' },
    ...(selectedPost
      ? [
          {
            label: selectedPost.title.rendered,
            value: selectedPost.link,
          },
        ]
      : []),
    ...posts
      .filter((post) => post.link !== buttonLink)
      .map((post) => ({
        label: post.title.rendered,
        value: post.link,
      })),
  ];

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Contenu', 'amnesty')}>
          <TextControl
            label={__('Titre', 'amnesty')}
            value={title}
            onChange={(value) => setAttributes({ title: value })}
          />
          <TextareaControl
            label={__('Texte', 'amnesty')}
            value={text}
            onChange={(value) => setAttributes({ text: value })}
          />
          <SelectControl
            label={__('Couleur de fond', 'amnesty')}
            value={bgColor}
            options={[
              { label: __('Blanc', 'amnesty'), value: 'white' },
              { label: __('Gris clair', 'amnesty'), value: 'grey-lighter' },
              { label: __('Gris', 'amnesty'), value: 'grey' },
              { label: __('Noir', 'amnesty'), value: 'black' },
            ]}
            onChange={(value) => setAttributes({ bgColor: value })}
          />
          <ToggleControl
            label={__('Afficher l’image', 'amnesty')}
            checked={showImage}
            onChange={(value) => setAttributes({ showImage: value })}
          />
          {showImage && (
            <>
              <MediaUpload
                onSelect={onSelectImage}
                allowedTypes={['image']}
                value={mediaId}
                render={({ open }) => (
                  <Button onClick={open} isSecondary>
                    {mediaId
                      ? __('Changer la photo', 'amnesty')
                      : __('Ajouter une photo', 'amnesty')}
                  </Button>
                )}
              />
              <SelectControl
                label={__('Position de la photo', 'amnesty')}
                value={mediaPosition}
                options={[
                  { label: __('Gauche', 'amnesty'), value: 'left' },
                  { label: __('Droite', 'amnesty'), value: 'right' },
                ]}
                onChange={(value) => setAttributes({ mediaPosition: value })}
              />
            </>
          )}
        </PanelBody>

        <PanelBody title={__('Icônes', 'amnesty')}>
          {icons.map((iconItem, index) => (
            <div
              key={index}
              style={{ border: '1px solid #eee', padding: '10px', marginBottom: '10px' }}
            >
              <SelectControl
                label={`Icône ${index + 1}`}
                value={iconItem.icon}
                options={[{ label: __('Choisir une icône', 'amnesty'), value: '' }, ...iconOptions]}
                onChange={(value) => updateIcon(index, 'icon', value)}
              />
              {iconItem.icon && (
                <TextControl
                  label={`Texte sous l'icône ${index + 1}`}
                  value={iconItem.text}
                  onChange={(value) => updateIcon(index, 'text', value)}
                />
              )}
              <Button isDestructive onClick={() => removeIcon(index)}>
                {__('Supprimer cette icône', 'amnesty')}
              </Button>
            </div>
          ))}
          {icons.length < 4 && (
            <Button isPrimary onClick={addIcon}>
              {__('Ajouter une icône', 'amnesty')}
            </Button>
          )}
        </PanelBody>

        <PanelBody title={__('Bouton', 'amnesty')}>
          <ToggleControl
            label={__('Afficher bouton', 'amnesty')}
            checked={displayButton}
            onChange={(value) => setAttributes({ displayButton: value })}
          />
          {displayButton && (
            <>
              <TextControl
                label={__('Label du bouton', 'amnesty')}
                value={buttonLabel}
                onChange={(value) => setAttributes({ buttonLabel: value })}
              />
              {loading ? (
                <p>{__('Chargement des posts', 'amnesty')}</p>
              ) : (
                <SelectControl
                  label={__('Lien du bouton', 'amnesty')}
                  value={buttonLink || ''}
                  options={postOptions}
                  onChange={handlePostSelect}
                />
              )}
              <SelectControl
                label={__('Position du bouton', 'amnesty')}
                value={buttonPosition}
                options={[
                  { label: __('Gauche', 'amnesty'), value: 'left' },
                  { label: __('Droite', 'amnesty'), value: 'right' },
                  { label: __('Centre', 'amnesty'), value: 'center' },
                ]}
                onChange={(value) => setAttributes({ buttonPosition: value })}
              />
            </>
          )}
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className={classnames('section-home', bgColor)}>
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
            <div className="icons">
              {icons.map((iconItem, index) => (
                <div key={index}>{renderIconBlock(iconItem)}</div>
              ))}
            </div>
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
    </>
  );
};

export default EditComponent;
