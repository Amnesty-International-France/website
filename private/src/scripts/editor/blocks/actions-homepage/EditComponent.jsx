import PostSearchControl from '../../components/PostSearchControl.jsx';

const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload } = wp.blockEditor;
const { PanelBody, TextControl, TextareaControl, Button, SelectControl, ToggleControl } =
  wp.components;
const { useEffect } = wp.element;
const apiFetch = wp.apiFetch;

const allowedTypesForThisBlock = [
  'post',
  'pages',
  'fiche_pays',
  'landmark',
  'local-structures',
  'petition',
  'press-release',
  'training',
  'document',
  'edh',
  'chronique',
  'tribe_events',
];

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

  const handlePostSelect = (index, post) => {
    console.log('[Bloc Actions] handlePostSelect pour item', index, post);

    const newItems = [...items];

    if (post) {
      if (!post.link || !post.title || !post.title.rendered) {
        console.error('[Bloc Actions] Objet post reçu est incomplet!', post);
        return;
      }

      const updatedItem = {
        ...newItems[index],
        internalUrl: post.link,
        internalUrlTitle: post.title.rendered,
        postId: post.id,
        postType: post.type,
      };

      newItems[index] = updatedItem;

      setAttributes({ items: newItems });
    } else {
      console.log('[Bloc Actions] Post est nul, suppression du lien.');
      const clearedItem = {
        ...newItems[index],
        internalUrl: '',
        internalUrlTitle: '',
        postId: 0,
        postType: '',
      };
      newItems[index] = clearedItem;
      setAttributes({ items: newItems });
    }
  };

  const handleRemoveLink = (index) => {
    const newItems = [...items];

    const clearedItem = {
      ...newItems[index],
      internalUrl: '',
      internalUrlTitle: '',
      postId: 0,
      postType: '',
    };

    newItems[index] = clearedItem;

    setAttributes({ items: newItems });
  };

  useEffect(() => {
    items.forEach((item, index) => {
      if (item.postId && !item.internalUrl) {
        apiFetch({ path: `/wp/v2/posts/${item.postId}?_fields=link,title,type` })
          .catch(() => apiFetch({ path: `/wp/v2/pages/${item.postId}?_fields=link,title,type` }))
          .then((post) => {
            if (post) {
              const newItems = [...items];
              newItems[index] = {
                ...newItems[index],
                internalUrl: post.link,
                internalUrlTitle: post.title.rendered,
                postType: post.type,
              };
              setAttributes({ items: newItems });
            }
          })
          .catch(() => {
            console.warn(
              `[Bloc Actions] Impossible de trouver le contenu pour le postId: ${item.postId} (Item ${index})`,
            );
          });
      }
    });
  }, []);

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
                label={__('Type de lien', 'amnesty')}
                value={item.linkType || 'external'}
                options={[
                  { label: 'Lien interne (contenu du site)', value: 'internal' },
                  { label: 'Lien externe (URL)', value: 'external' },
                ]}
                onChange={(value) => updateItem(index, 'linkType', value)}
              />

              {item.linkType === 'internal' ? (
                <>
                  {!item.internalUrl ? (
                    <PostSearchControl
                      onPostSelect={(post) => handlePostSelect(index, post)}
                      allowedTypes={allowedTypesForThisBlock}
                    />
                  ) : (
                    <div
                      style={{ marginTop: '10px', paddingTop: '10px', borderTop: '1px solid #ccc' }}
                    >
                      <p style={{ margin: 0 }}>
                        {__('Lien sélectionné :', 'amnesty')}{' '}
                        <strong dangerouslySetInnerHTML={{ __html: item.internalUrlTitle }} />
                      </p>
                      <Button isLink isDestructive onClick={() => handleRemoveLink(index)}>
                        {__('Retirer le lien', 'amnesty')}
                      </Button>
                    </div>
                  )}
                </>
              ) : (
                <TextControl
                  label={__('URL du lien externe', 'amnesty')}
                  value={item.externalUrl}
                  placeholder="https://exemple.com"
                  onChange={(value) => updateItem(index, 'externalUrl', value)}
                />
              )}

              {(item.internalUrl || item.externalUrl) && (
                <ToggleControl
                  label={__('Ouvrir dans un nouvel onglet', 'amnesty')}
                  checked={!!item.targetBlank}
                  onChange={(value) => updateItem(index, 'targetBlank', value)}
                />
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
            {items.map((item, index) => {
              const linkUrl = item.linkType === 'internal' ? item.internalUrl : item.externalUrl;
              const openInNewTab = !!item.targetBlank;

              return (
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
                      href={linkUrl || '#'}
                      className="item-button"
                      target={openInNewTab ? '_blank' : undefined}
                      rel={openInNewTab ? 'noopener noreferrer' : undefined}
                      onClick={(e) => {
                        if (linkUrl) e.preventDefault();
                      }}
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
              );
            })}
          </div>
        </div>
      </section>
    </>
  );
};

export default EditComponent;
