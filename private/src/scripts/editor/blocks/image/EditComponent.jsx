const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, Button, ToggleControl } = wp.components;
const { useState } = wp.element;
const { useSelect } = wp.data;

const normaliseMedia = (media) => ({
  ...media,
  source_url: media.source_url || media.url || media.sizes?.full?.url || '',
  alt_text: media.alt_text || media.alt || '',
  caption: typeof media.caption === 'string' ? { raw: media.caption } : media.caption,
  description:
    typeof media.description === 'string' ? { raw: media.description } : media.description,
});

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const { mediaId, mediaMobileId, fullWidth, className = '' } = attributes;
  const showMetadata = !className.includes('is-style-simple');
  const [selectedMediaOverride, setSelectedMediaOverride] = useState(null);
  const [selectedMobileMediaOverride, setSelectedMobileMediaOverride] = useState(null);

  const selectedMedia = useSelect(
    (select) => (mediaId ? select('core').getMedia(mediaId) : null),
    [mediaId],
  );

  const selectedMobileMedia = useSelect(
    (select) => (mediaMobileId ? select('core').getMedia(mediaMobileId) : null),
    [mediaMobileId],
  );

  const desktopMedia =
    selectedMediaOverride?.id === mediaId ? selectedMediaOverride : selectedMedia;
  const mobileMedia =
    selectedMobileMediaOverride?.id === mediaMobileId
      ? selectedMobileMediaOverride
      : selectedMobileMedia;
  const previewMedia = desktopMedia || mobileMedia;
  const previewCaption = desktopMedia?.caption?.raw || mobileMedia?.caption?.raw;
  const previewDescription =
    desktopMedia?.description?.raw ||
    desktopMedia?.description?.rendered ||
    mobileMedia?.description?.raw ||
    mobileMedia?.description?.rendered;
  const blockProps = useBlockProps({
    className: ['image-block', className, fullWidth ? 'image-fullwidth' : '']
      .filter(Boolean)
      .join(' '),
  });

  const onSelectImage = (attribute, setOverride) => (newMedia) => {
    setOverride(normaliseMedia(newMedia));
    setAttributes({ [attribute]: newMedia.id });
  };

  const renderMediaControl = (label, attribute, value, setOverride) => (
    <div className="image-editor-control">
      <p className="image-editor-control-label">{label}</p>
      <MediaUploadCheck>
        <MediaUpload
          onSelect={onSelectImage(attribute, setOverride)}
          allowedTypes={['image']}
          value={value}
          render={({ open }) => (
            <Button onClick={open} isPrimary>
              {value ? __('Changer l’image', 'amnesty') : __('Ajouter une image', 'amnesty')}
            </Button>
          )}
        />
      </MediaUploadCheck>
      {value && (
        <Button
          isSecondary
          isDestructive
          onClick={() => {
            setOverride(null);
            setAttributes({ [attribute]: null });
          }}
        >
          {__('Retirer l’image', 'amnesty')}
        </Button>
      )}
    </div>
  );

  const renderPreview = (label, media) =>
    media && (
      <div className="image-editor-preview">
        <p className="image-editor-preview-label">{label}</p>
        <div className="image-wrapper">
          <img
            src={media.source_url || media.url || media.sizes?.full?.url || ''}
            alt={media.alt_text || media.alt || ''}
          />
          {showMetadata && previewCaption && <p className="image-caption">{previewCaption}</p>}
        </div>
      </div>
    );

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres de l’image', 'amnesty')}>
          <ToggleControl
            label={__('Pleine largeur', 'amnesty')}
            checked={fullWidth}
            onChange={(value) => setAttributes({ fullWidth: value })}
          />
          {renderMediaControl(
            __('Image desktop/tablette', 'amnesty'),
            'mediaId',
            mediaId,
            setSelectedMediaOverride,
          )}
          {renderMediaControl(
            __('Image mobile', 'amnesty'),
            'mediaMobileId',
            mediaMobileId,
            setSelectedMobileMediaOverride,
          )}
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        {previewMedia ? (
          <>
            {desktopMedia && mobileMedia ? (
              <div className="image-editor-preview-list">
                {renderPreview(__('Desktop/tablette', 'amnesty'), desktopMedia)}
                {renderPreview(__('Mobile', 'amnesty'), mobileMedia)}
              </div>
            ) : (
              renderPreview(__('Tous les devices', 'amnesty'), previewMedia)
            )}
            {showMetadata && previewDescription && (
              <p className="image-description">{previewDescription}</p>
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
