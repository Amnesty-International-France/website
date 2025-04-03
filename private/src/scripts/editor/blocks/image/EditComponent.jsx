const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload } = wp.blockEditor;
const { PanelBody, Button } = wp.components;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const { imageUrl, altText, caption, description } = attributes;

  const onSelectImage = (media) => {
    setAttributes({
      imageUrl: media.url,
      altText: media.alt,
      caption: media.caption,
      description: media.description,
    });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres de l’image', 'amnesty')}>
          <MediaUpload
            onSelect={onSelectImage}
            allowedTypes={['image']}
            render={({ open }) => (
              <Button onClick={open} isPrimary>
                {imageUrl ? __('Changer l’image', 'amnesty') : __('Ajouter une image', 'amnesty')}
              </Button>
            )}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className="image-block">
        {imageUrl && (
          <>
            <div className="image-wrapper">
              <img src={imageUrl} alt={altText} />
              {caption && <p className="image-caption">{caption}</p>}
            </div>
            {description && <p className="image-description">{description}</p>}
          </>
        )}
      </div>
    </>
  );
};

export default EditComponent;
