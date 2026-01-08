const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, Button, ToggleControl } = wp.components;
const { useSelect } = wp.data;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const { mediaId, fullWidth } = attributes;

  const selectedMedia = useSelect(
    (select) => (mediaId ? select('core').getMedia(mediaId) : null),
    [mediaId],
  );

  const onSelectImage = (newMedia) => {
    setAttributes({ mediaId: newMedia.id });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres de l’image', 'amnesty')}>
          <ToggleControl
            label="Full largeur"
            checked={fullWidth}
            onChange={(value) => setAttributes({ fullWidth: value })}
          />
          <MediaUploadCheck>
            <MediaUpload
              onSelect={onSelectImage}
              allowedTypes={['image']}
              value={mediaId}
              render={({ open }) => (
                <Button onClick={open} isPrimary>
                  {mediaId ? __('Changer l’image', 'amnesty') : __('Ajouter une image', 'amnesty')}
                </Button>
              )}
            />
          </MediaUploadCheck>
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className={`image-block ${fullWidth && 'image-fullwidth'}`}>
        {selectedMedia ? (
          <>
            <div className="image-wrapper">
              <img src={selectedMedia.source_url} alt={selectedMedia.alt_text || ''} />
              {selectedMedia.caption && (
                <p className="image-caption">{selectedMedia.caption.raw}</p>
              )}
            </div>
            {selectedMedia.description && (
              <p className="image-description">
                {selectedMedia.description.raw || selectedMedia.description.rendered}
              </p>
            )}
          </>
        ) : (
          <p>{__('Aucune image sélectionnée', 'amnesty')}</p>
        )}
      </div>
    </>
  );
};

export default EditComponent;
