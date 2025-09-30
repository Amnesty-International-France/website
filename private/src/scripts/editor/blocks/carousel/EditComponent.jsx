const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, Button, Notice } = wp.components;
const { useSelect } = wp.data;
const { useState } = wp.element;

const EditComponent = ({ attributes, setAttributes }) => {
  const { mediaIds = [] } = attributes;
  const [errorMessage, setErrorMessage] = useState(null);
  const selectedMedia = useSelect(
    (select) =>
      mediaIds.length > 0 ? mediaIds.map((id) => select('core').getMedia(id)).filter(Boolean) : [],
    [mediaIds],
  );

  const onSelectImages = (newMedia) => {
    if (newMedia.length < 5) {
      setErrorMessage(__('Veuillez sélectionner au moins 5 images.', 'amnesty'));
      setAttributes({ mediaIds: [] });
    } else {
      setErrorMessage(null);
      setAttributes({ mediaIds: newMedia.map((img) => img.id) });
    }
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du carrousel', 'amnesty')} initialOpen={true}>
          <MediaUploadCheck>
            <MediaUpload
              onSelect={onSelectImages}
              allowedTypes={['image']}
              multiple
              gallery
              value={mediaIds}
              render={({ open }) => (
                <Button onClick={open} isSecondary>
                  {mediaIds.length > 0
                    ? __('Modifier les images', 'amnesty')
                    : __('Sélectionner des images', 'amnesty')}
                </Button>
              )}
            />
          </MediaUploadCheck>
          {errorMessage && (
            <Notice status="error" isDismissible={false}>
              {errorMessage}
            </Notice>
          )}
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className="carousel-block">
        {selectedMedia.length > 0 ? (
          <div className="swiper">
            <div className="swiper-wrapper">
              {selectedMedia.map((img) => (
                <div key={img.id} className="swiper-slide">
                  <img src={img.source_url} alt={img.alt_text || ''} />
                </div>
              ))}
            </div>
            <div className="carousel-nav prev">&#10094;</div>
            <div className="carousel-nav next">&#10095;</div>
          </div>
        ) : (
          <p>
            {__(
              "Aucune image sélectionnée ou pas assez d'images sélectionnées (minimum 5).",
              'amnesty',
            )}
          </p>
        )}
      </div>
    </>
  );
};

export default EditComponent;
