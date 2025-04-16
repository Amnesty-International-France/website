import classnames from 'classnames';

const { __ } = wp.i18n;
const { useSelect } = wp.data;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, ToggleControl, Button, SelectControl, TextControl } = wp.components;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const { showImage, imageId, quoteText, author, bgColor, size } = attributes;

  const image = useSelect(
    (select) => {
      if (!imageId) return null;
      return select('core').getMedia(imageId);
    },
    [imageId],
  );

  const imageUrl = image?.source_url;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres de la citation', 'amnesty')}>
          <TextControl
            label={__('Citation', 'amnesty')}
            value={quoteText}
            onChange={(value) => setAttributes({ quoteText: value })}
          />
          <TextControl
            label={__('Auteur', 'amnesty')}
            value={author}
            onChange={(value) => setAttributes({ author: value })}
          />
        </PanelBody>

        <PanelBody title={__('Styles de la citation', 'amnesty')}>
          <ToggleControl
            label={__('Afficher l’image', 'amnesty')}
            checked={showImage}
            onChange={(value) => setAttributes({ showImage: value })}
          />

          {showImage && (
            <MediaUploadCheck>
              <MediaUpload
                onSelect={(media) => setAttributes({ imageId: media.id })}
                allowedTypes={['image']}
                render={({ open }) => (
                  <Button onClick={open} isSecondary>
                    {__('Choisir une image', 'amnesty')}
                  </Button>
                )}
              />
            </MediaUploadCheck>
          )}

          <SelectControl
            label={__('Taille', 'amnesty')}
            value={size}
            options={[
              { label: __('Petit', 'amnesty'), value: 'small' },
              { label: __('Moyen', 'amnesty'), value: 'medium' },
              { label: __('Grand', 'amnesty'), value: 'large' },
            ]}
            onChange={(value) => setAttributes({ size: value })}
          />

          <SelectControl
            label={__('Couleur de fond', 'amnesty')}
            value={bgColor}
            options={[
              { label: __('Jaune', 'amnesty'), value: 'yellow' },
              { label: __('Transparent', 'amnesty'), value: 'transparent' },
              { label: __('Blanc', 'amnesty'), value: 'white' },
              { label: __('Gris', 'amnesty'), value: 'gray' },
              { label: __('Noir', 'amnesty'), value: 'black' },
            ]}
            onChange={(value) => setAttributes({ bgColor: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className="quote-block">
        {showImage && imageUrl && (
          <div className="quote-image">
            <img src={imageUrl} alt={__('Image de la citation', 'amnesty')} />
          </div>
        )}
        <div className={classnames('quote-content', bgColor)}>
          <blockquote className={classnames('text', size)}>{quoteText}</blockquote>
          {author && <p className={classnames('author', size)}>{author}</p>}
        </div>
      </div>
    </>
  );
};

export default EditComponent;
