const { __ } = wp.i18n;
const { useEffect } = wp.element;
const { useDispatch, useSelect } = wp.data;
const {
  useBlockProps,
  useInnerBlocksProps,
  InspectorControls,
  MediaUpload,
  MediaUploadCheck,
  URLInput,
} = wp.blockEditor;
const { PanelBody, TextControl, ToggleControl, Button, Placeholder } = wp.components;

const ALLOWED_BLOCKS = ['core/paragraph', 'core/list', 'core/list-item'];
const TEMPLATE = [['core/paragraph', { placeholder: __('Descriptif…', 'amnesty') }]];

const ArrowIcon = () => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    width="16"
    height="16"
    viewBox="0 0 16 16"
    fill="none"
    aria-hidden="true"
  >
    <path
      fillRule="evenodd"
      clipRule="evenodd"
      d="M10.7826 7.33336L7.20663 3.75736L8.1493 2.8147L13.3346 8.00003L8.1493 13.1854L7.20663 12.2427L10.7826 8.6667H2.66797V7.33336H10.7826Z"
      fill="black"
    />
  </svg>
);

const EditComponent = ({ attributes, setAttributes, clientId }) => {
  const { thumbnail, surtitle, title, permalink, linkTitle, newTab } = attributes;

  const image = useSelect(
    (select) => (thumbnail ? select('core').getMedia(thumbnail) : null),
    [thumbnail],
  );

  // The image is required: saving the post is blocked until one is chosen.
  const { lockPostSaving, unlockPostSaving } = useDispatch('core/editor');
  useEffect(() => {
    const lockName = `card-rich-text-thumbnail-${clientId}`;
    if (thumbnail) {
      unlockPostSaving(lockName);
      return undefined;
    }
    lockPostSaving(lockName);
    return () => unlockPostSaving(lockName);
  }, [thumbnail, clientId]);

  const renderMediaUpload = (render) => (
    <MediaUploadCheck>
      <MediaUpload
        onSelect={(media) => setAttributes({ thumbnail: media.id })}
        allowedTypes={['image']}
        value={thumbnail}
        render={render}
      />
    </MediaUploadCheck>
  );

  const blockProps = useBlockProps({ className: 'card-rich-text-block' });
  const innerBlocksProps = useInnerBlocksProps(
    { className: 'card-rich-text-content-text' },
    { allowedBlocks: ALLOWED_BLOCKS, template: TEMPLATE, templateLock: false },
  );

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Visuel', 'amnesty')}>
          {renderMediaUpload(({ open }) => (
            <Button onClick={open} isPrimary>
              {thumbnail ? __('Changer l’image', 'amnesty') : __('Ajouter une image', 'amnesty')}
            </Button>
          ))}
          {thumbnail && (
            <Button onClick={() => setAttributes({ thumbnail: null })} isDestructive>
              {__('Supprimer l’image', 'amnesty')}
            </Button>
          )}
          {!thumbnail && (
            <p className="components-base-control__help">
              {__('L’image est obligatoire pour enregistrer la page.', 'amnesty')}
            </p>
          )}
        </PanelBody>
        <PanelBody title={__('Contenu', 'amnesty')}>
          <TextControl
            label={__('Surtitre', 'amnesty')}
            value={surtitle}
            onChange={(value) => setAttributes({ surtitle: value })}
            placeholder={__('Entrez un surtitre…', 'amnesty')}
          />
          <TextControl
            label={__('Titre', 'amnesty')}
            value={title}
            onChange={(value) => setAttributes({ title: value })}
            placeholder={__('Entrez un titre…', 'amnesty')}
            help={__('Le descriptif se saisit directement dans la carte (texte riche).', 'amnesty')}
          />
        </PanelBody>
        <PanelBody title={__('Lien', 'amnesty')}>
          <URLInput
            label={__('Lien', 'amnesty')}
            value={permalink}
            onChange={(value) => setAttributes({ permalink: value })}
            placeholder="https://"
            isFullWidth
          />
          <TextControl
            label={__('Libellé du lien', 'amnesty')}
            value={linkTitle}
            onChange={(value) => setAttributes({ linkTitle: value })}
            placeholder={__('Voir la suite', 'amnesty')}
          />
          <ToggleControl
            label={__('Ouvrir dans un nouvel onglet', 'amnesty')}
            checked={newTab}
            onChange={(value) => setAttributes({ newTab: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div className="card-rich-text-thumbnail-wrapper">
          {image?.source_url && (
            <img className="card-rich-text-thumbnail" src={image.source_url} alt="" />
          )}
          {!thumbnail && (
            <Placeholder
              className="card-rich-text-thumbnail-placeholder"
              icon="format-image"
              label={__('Image obligatoire', 'amnesty')}
              instructions={__('Ajoutez une image pour pouvoir enregistrer la page.', 'amnesty')}
            >
              {renderMediaUpload(({ open }) => (
                <Button onClick={open} isPrimary>
                  {__('Ajouter une image', 'amnesty')}
                </Button>
              ))}
            </Placeholder>
          )}
        </div>
        <div className="card-rich-text-content">
          {surtitle && <p className="card-rich-text-content-surtitle">{surtitle}</p>}
          {title && <p className="card-rich-text-content-title">{title}</p>}
          <div {...innerBlocksProps} />
          {permalink && linkTitle && (
            <div className="card-rich-text-content-see-more">
              <span className="card-rich-text-content-see-more-link">
                <ArrowIcon />
                <span className="card-rich-text-content-see-more-label">{linkTitle}</span>
              </span>
            </div>
          )}
        </div>
      </div>
    </>
  );
};

export default EditComponent;
