const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, Button } = wp.components;
const { useSelect } = wp.data;

const EditComponent = ({ attributes, setAttributes }) => {
  const { mediaIds } = attributes;

  const selectedMedia = useSelect(
    (select) =>
      mediaIds.length > 0 ? mediaIds.map((id) => select('core').getMedia(id)).filter(Boolean) : [],
    [mediaIds],
  );

  const onSelectImages = (newMedia) => {
    setAttributes({ mediaIds: newMedia.map((img) => img.id) });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du carousel', 'amnesty')} initialOpen={true}>
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
          <p>{__('Aucune image sélectionnée', 'amnesty')}</p>
        )}
      </div>
    </>
  );
};

export default EditComponent;
