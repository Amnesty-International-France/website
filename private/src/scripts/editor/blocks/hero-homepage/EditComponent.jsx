import CustomButton from '../button/Button.jsx';

const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck, URLInput } =
  wp.blockEditor;
const { PanelBody, BaseControl, Button, TextControl } = wp.components;
const { useSelect } = wp.data;

const useMedias = (mediaIds) =>
  useSelect(
    (select) =>
      !mediaIds?.length ? [] : mediaIds.map((id) => (id ? select('core').getMedia(id) : null)),
    [mediaIds],
  );

const EditComponent = ({ attributes, setAttributes }) => {
  const { items = [] } = attributes;

  const updateItem = (index, key, value) => {
    const newItems = [...items];
    newItems[index] = { ...newItems[index], [key]: value };
    setAttributes({ items: newItems });
  };

  const mediaIds = items.map((item) => item.mediaId);
  const mediaList = useMedias(mediaIds);

  const mediaMobileIds = items.map((item) => item.mediaMobileId);
  const mediaMobileList = useMedias(mediaMobileIds);

  const visibleItem = items[0];
  const visibleMedia = mediaList[0];
  const visibleMobileMedia = mediaMobileList[0];

  return (
    <>
      <InspectorControls>
        {items.map((item, index) => {
          const itemMedia = mediaList[index];
          const itemMobileMedia = mediaMobileList[index];

          return (
            <PanelBody key={index} title={`Bloc ${index + 1}`} initialOpen={index === 0}>
              <BaseControl label="Image deskstop">
                <MediaUploadCheck>
                  <MediaUpload
                    onSelect={(selectedMedia) => updateItem(index, 'mediaId', selectedMedia.id)}
                    allowedTypes={['image']}
                    value={item.mediaId}
                    render={({ open }) => (
                      <Button onClick={open} isSecondary>
                        {item.mediaId
                          ? __('Changer l’image', 'amnesty')
                          : __('Ajouter une image', 'amnesty')}
                      </Button>
                    )}
                  />
                </MediaUploadCheck>

                {itemMedia?.source_url && (
                  <img
                    src={itemMedia.source_url}
                    alt=""
                    style={{ maxWidth: '100%', height: 'auto', marginTop: '8px' }}
                  />
                )}
              </BaseControl>

              <BaseControl label="Image mobile">
                <MediaUploadCheck>
                  <MediaUpload
                    onSelect={(selectedMedia) =>
                      updateItem(index, 'mediaMobileId', selectedMedia.id)
                    }
                    allowedTypes={['image']}
                    value={item.mediaMobileId}
                    render={({ open }) => (
                      <Button onClick={open} isSecondary>
                        {item.mediaMobileId
                          ? __('Changer l’image', 'amnesty')
                          : __('Ajouter une image', 'amnesty')}
                      </Button>
                    )}
                  />
                </MediaUploadCheck>

                {itemMobileMedia?.source_url && (
                  <img
                    src={itemMobileMedia.source_url}
                    alt=""
                    style={{ maxWidth: '100%', height: 'auto', marginTop: '8px' }}
                  />
                )}
              </BaseControl>

              <TextControl
                label={__('Sous titre', 'amnesty')}
                value={item.subtitle}
                onChange={(value) => updateItem(index, 'subtitle', value)}
              />

              <TextControl
                label={__('Label du bouton', 'amnesty')}
                value={item.buttonLabel}
                onChange={(value) => updateItem(index, 'buttonLabel', value)}
              />

              <URLInput
                label={__('URL du bouton', 'amnesty')}
                value={item.buttonUrl}
                onChange={(value) => updateItem(index, 'buttonUrl', value)}
              />
            </PanelBody>
          );
        })}
      </InspectorControls>

      <div {...useBlockProps()} className="hero-homepage">
        {visibleItem ? (
          <div className="item">
            <div className="hero-wrapper">
              <div className="hero-image-wrapper">
                <img className="hero-image" src={visibleMedia?.source_url || ''} alt="" />
              </div>
              <div className="hero-image-mobile-wrapper">
                <img className="hero-image" src={visibleMobileMedia?.source_url || ''} alt="" />
              </div>
              <div className="hero-content-wrapper">
                <h1 className="hero-title">
                  On se bat ensemble,
                  <br />
                  on gagne ensemble.
                </h1>
                {visibleItem.subtitle && <h3 className="hero-subtitle">{visibleItem.subtitle}</h3>}
                {visibleItem.buttonUrl && visibleItem.buttonLabel && (
                  <CustomButton
                    icon="arrow-right"
                    label={visibleItem.buttonLabel}
                    size="medium"
                    link={visibleItem.buttonUrl}
                    style="bg-yellow"
                    alignment="center"
                  />
                )}
              </div>
            </div>
          </div>
        ) : (
          <p>{__('Aucune image sélectionnée', 'amnesty')}</p>
        )}
      </div>
    </>
  );
};

export default EditComponent;
