const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload } = wp.blockEditor;
const { PanelBody, TextControl, TextareaControl, Button, SelectControl, Spinner } = wp.components;
const { useSelect } = wp.data;

const EditComponent = ({ attributes, setAttributes }) => {
  const { title, chapo, items } = attributes;
  const blockProps = useBlockProps();

  const updateItem = (index, key, value) => {
    const newItems = [...items];
    newItems[index] = {
      ...newItems[index],
      [key]: value,
    };
    setAttributes({ items: newItems });
  };

  const onSelectImage = (index, media) => {
    updateItem(index, 'imageUrl', media.url);
  };

  const pages = useSelect((select) => {
    const { getEntityRecords } = select('core');
    const pagesList = getEntityRecords('postType', 'page', {
      per_page: -1,
      _fields: 'id,title,link',
      status: 'publish',
    });
    return pagesList;
  }, []);

  const pageOptions = [{ label: __('Sélectionner une page', 'amnesty'), value: '' }];
  if (pages) {
    pages.forEach((page) => {
      if (page.title && page.title.rendered) {
        pageOptions.push({ label: page.title.rendered, value: page.link });
      }
    });
  }

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du Bloc Actions', 'amnesty')}>
          <TextControl
            label={__('Titre du bloc', 'amnesty')}
            value={title}
            onChange={(newTitle) => setAttributes({ title: newTitle })}
            placeholder={__('Saisissez le titre du bloc', 'amnesty')}
          />

          <TextareaControl
            label={__('Chapô du bloc', 'amnesty')}
            value={chapo}
            onChange={(newChapo) => setAttributes({ chapo: newChapo })}
            placeholder={__('Saisissez le chapô du bloc', 'amnesty')}
            help={__('Une courte description pour le bloc entier.', 'amnesty')}
          />

          {items.map((item, index) => (
            <PanelBody key={index} title={`Bloc ${index + 1}`} initialOpen={index === 0}>
              <TextControl
                label="Titre"
                value={item.itemTitle}
                onChange={(newTitle) => updateItem(index, 'itemTitle', newTitle)}
                placeholder={__('Titre du bloc', 'amnesty')}
              />
              <TextareaControl
                label="Description"
                value={item.itemDescription}
                onChange={(newDescription) => updateItem(index, 'itemDescription', newDescription)}
                placeholder={__('Description du bloc', 'amnesty')}
                rows={3}
              />
              <TextControl
                label="Libellé du bouton"
                value={item.buttonLabel}
                onChange={(newLabel) => updateItem(index, 'buttonLabel', newLabel)}
                placeholder={__('Ex: En savoir plus', 'amnesty')}
              />
              <SelectControl
                label={__('Lien du bouton (page du site)', 'amnesty')}
                value={item.buttonLink}
                options={pageOptions}
                onChange={(newLink) => updateItem(index, 'buttonLink', newLink)}
                help={!pages && <Spinner />}
              />
              {item.buttonLink && item.buttonLink.startsWith('http') && (
                <p className="amnesty-link-preview">
                  {__('Lien actuel:', 'amnesty')}{' '}
                  <a href={item.buttonLink} target="_blank" rel="noopener noreferrer">
                    {item.buttonLink}
                  </a>
                </p>
              )}
              <div className="amnesty-image-upload-section">
                <p className="amnesty-image-upload-label">{__("Image de l'item", 'amnesty')}</p>
                {item.imageUrl && (
                  <img
                    src={item.imageUrl}
                    alt={__("Image de l'item", 'amnesty')}
                    className="amnesty-image-preview"
                  />
                )}
                <MediaUpload
                  onSelect={(media) => onSelectImage(index, media)}
                  allowedTypes={['image']}
                  value={item.imageUrl}
                  render={({ open }) => (
                    <Button isSecondary onClick={open} className="amnesty-media-upload-button">
                      {item.imageUrl
                        ? __("Changer l'image", 'amnesty')
                        : __('Sélectionner une image', 'amnesty')}
                    </Button>
                  )}
                />
                {item.imageUrl && (
                  <Button
                    isLink
                    isDestructive
                    onClick={() => updateItem(index, 'imageUrl', '')}
                    className="amnesty-remove-image-button"
                  >
                    {__("Supprimer l'image", 'amnesty')}
                  </Button>
                )}
              </div>
            </PanelBody>
          ))}
        </PanelBody>
      </InspectorControls>

      <section {...blockProps} className="actions-homepage">
        <div className="content">
          <h2 className="title">
            {title || __('Titre du bloc (à remplir dans les paramètres)', 'amnesty')}
          </h2>
          {chapo && <p className="chapo">{chapo}</p>}
          <div className="items">
            {items.map((item, index) => (
              <div key={index} className="item">
                <div className="image-wrapper">
                  {item.imageUrl ? (
                    <img className="image" src={item.imageUrl} alt={item.itemTitle || ''} />
                  ) : (
                    <div className="no-image">
                      <span>{__('Sélectionnez une image', 'amnesty')}</span>
                    </div>
                  )}
                  <div className="item-title-wrapper">
                    <h3 className="item-title">{item.itemTitle || __('Titre', 'amnesty')}</h3>
                  </div>
                </div>
                <div className="item-content">
                  <span className="item-description">
                    {item.itemDescription || __('Description', 'amnesty')}
                  </span>
                  <a
                    href={item.buttonLink || '#'}
                    className="item-button"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    <div className="icon-container">
                      <svg
                        className="icon"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        strokeWidth="1.5"
                        stroke="currentColor"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                        />
                      </svg>
                    </div>
                    <span className="label">{item.buttonLabel || __('Lien', 'amnesty')}</span>
                  </a>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    </>
  );
};

export default EditComponent;
