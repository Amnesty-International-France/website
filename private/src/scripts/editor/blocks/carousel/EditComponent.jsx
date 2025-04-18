const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, Button } = wp.components;

const EditComponent = ({ attributes, setAttributes }) => {
  const { images } = attributes;

  const updateImages = (newImages) => {
    setAttributes({
      images: newImages.map((img) => ({
        id: img.id,
        url: img.url,
        alt: img.alt,
      })),
    });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du carousel', 'amnesty')} initialOpen={true}>
          <MediaUploadCheck>
            <MediaUpload
              onSelect={updateImages}
              allowedTypes={['image']}
              multiple
              gallery
              value={images.map((img) => img.id)}
              render={({ open }) => (
                <Button onClick={open} isSecondary>
                  {images.length > 0
                    ? __('Modifier les images', 'amnesty')
                    : __('Sélectionner des images', 'amnesty')}
                </Button>
              )}
            />
          </MediaUploadCheck>
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className="carousel-block">
        {images.length > 0 ? (
          <div className="swiper">
            <div className="swiper-wrapper">
              {images.map((img, i) => (
                <div key={i} className="swiper-slide">
                  <img src={img.url} alt={img.alt || ''} loading="lazy" />
                </div>
              ))}
            </div>
            <div className="carousel-nav prev">&#10094;</div>
            <div className="carousel-nav next">&#10095;</div>
          </div>
        ) : (
          <div className="carousel-placeholder">
            <p>{__('Aucune image sélectionnée', 'amnesty')}</p>
          </div>
        )}
      </div>
    </>
  );
};

export default EditComponent;
