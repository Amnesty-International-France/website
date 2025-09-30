const { __ } = wp.i18n;
const { useEffect, useState } = wp.element;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const {
  PanelBody,
  SelectControl,
  TextControl,
  Spinner,
  ToggleControl,
  Button: WpButton,
} = wp.components;
const { useSelect } = wp.data;
const apiFetch = wp.apiFetch;

const EditComponent = ({ attributes, setAttributes }) => {
  const {
    linkType = 'internal',
    externalUrl = '',
    externalLabel = '',
    postType = '',
    internalUrl = '',
    internalUrlTitle = '',
    postId = 0,
    targetBlank = false,
  } = attributes;

  const [searchTerm, setSearchTerm] = useState('');
  const [searchResults, setSearchResults] = useState([]);
  const [isSearching, setIsSearching] = useState(false);

  const allowedPostTypes = [
    'post',
    'page',
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

  const availablePostTypes = useSelect((select) => {
    const types = select('core').getPostTypes({ per_page: -1 });
    return types ? types.filter((type) => allowedPostTypes.includes(type.slug)) : [];
  }, []);

  const postTypeOptions = availablePostTypes.map((type) => ({
    label: type.name,
    value: type.slug,
  }));

  useEffect(() => {
    if (postId && !internalUrl) {
      apiFetch({ path: `/wp/v2/posts/${postId}?_fields=link,title,type` })
        .catch(() => apiFetch({ path: `/wp/v2/pages/${postId}?_fields=link,title,type` }))
        .catch(() => Promise.reject())
        .then((post) => {
          if (post) {
            setAttributes({
              internalUrl: post.link,
              internalUrlTitle: post.title.rendered,
              postType: post.type,
            });
          }
        })
        .catch(() => {
          console.warn(
            `"Lire aussi" block: Impossible de trouver le contenu pour le postId: ${postId}`,
          );
        });
    }
  }, [postId, internalUrl, setAttributes]);

  useEffect(() => {
    if (!searchTerm || searchTerm.length < 2 || !postType) {
      setSearchResults([]);
      return;
    }
    const selectedTypeObject = availablePostTypes.find((type) => type.slug === postType);
    if (!selectedTypeObject) return;

    const path = `/wp/v2/${selectedTypeObject.rest_base}?search=${encodeURIComponent(
      searchTerm,
    )}&per_page=10&_fields=id,title,link`;

    setIsSearching(true);
    apiFetch({ path })
      .then((items) => {
        setSearchResults(items);
        setIsSearching(false);
      })
      .catch(() => {
        setSearchResults([]);
        setIsSearching(false);
      });
  }, [searchTerm, postType, availablePostTypes]);

  const handleSelectPostType = (value) => {
    setAttributes({ postType: value, internalUrl: '', internalUrlTitle: '', postId: 0 });
    setSearchTerm('');
    setSearchResults([]);
  };

  const handleSelectSearchResult = (item) => {
    setAttributes({
      internalUrl: item.link,
      internalUrlTitle: item.title.rendered,
      postId: item.id,
    });
    setSearchTerm('');
    setSearchResults([]);
  };

  const handleRemoveLink = () => {
    setAttributes({ internalUrl: '', internalUrlTitle: '', postId: 0 });
  };

  let linkContent;
  const linkProps = {
    ...(targetBlank && { target: '_blank', rel: 'noopener noreferrer' }),
  };

  if (linkType === 'internal' && internalUrl) {
    linkContent = (
      <a href={internalUrl} {...linkProps} onClick={(e) => e.preventDefault()}>
        <span dangerouslySetInnerHTML={{ __html: internalUrlTitle }} />
      </a>
    );
  } else if (linkType === 'external' && externalUrl) {
    linkContent = (
      <a href={externalUrl} {...linkProps} onClick={(e) => e.preventDefault()}>
        {externalLabel || externalUrl}
      </a>
    );
  } else {
    linkContent = (
      <span>
        {linkType === 'external'
          ? __('Aucun lien externe fourni.', 'amnesty')
          : __('Aucun contenu interne sélectionné.', 'amnesty')}
      </span>
    );
  }

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du lien', 'amnesty')} initialOpen={true}>
          <SelectControl
            label={__('Type de lien', 'amnesty')}
            value={linkType}
            options={[
              { label: __('Interne (contenu du site)', 'amnesty'), value: 'internal' },
              { label: __('Externe (URL)', 'amnesty'), value: 'external' },
            ]}
            onChange={(value) => setAttributes({ linkType: value })}
          />

          {linkType === 'internal' && (
            <>
              <SelectControl
                label={__('Type de contenu', 'amnesty')}
                value={postType}
                options={[
                  { label: __('Choisir un type', 'amnesty'), value: '' },
                  ...postTypeOptions,
                ]}
                onChange={handleSelectPostType}
              />
              {postType && !internalUrl && (
                <div style={{ marginTop: '10px' }}>
                  <TextControl
                    label={__('Rechercher un contenu', 'amnesty')}
                    value={searchTerm}
                    onChange={setSearchTerm}
                    placeholder={__('Tapez au moins 2 caractères', 'amnesty')}
                  />
                  {isSearching && <Spinner />}
                  {!isSearching && searchResults.length > 0 && (
                    <ul
                      style={{
                        border: '1px solid #ccc',
                        padding: 5,
                        maxHeight: 150,
                        overflowY: 'auto',
                        margin: 0,
                        listStyle: 'none',
                      }}
                    >
                      {searchResults.map((item) => (
                        <li
                          key={item.id}
                          style={{
                            cursor: 'pointer',
                            padding: '8px 10px',
                            borderBottom: '1px solid #eee',
                          }}
                          onClick={() => handleSelectSearchResult(item)}
                        >
                          <span dangerouslySetInnerHTML={{ __html: item.title.rendered }} />
                        </li>
                      ))}
                    </ul>
                  )}
                </div>
              )}
              {internalUrl && (
                <div style={{ marginTop: '10px', paddingTop: '10px', borderTop: '1px solid #ccc' }}>
                  <p style={{ margin: '0 0 8px 0' }}>
                    {__('Lien sélectionné :', 'amnesty')}{' '}
                    <strong dangerouslySetInnerHTML={{ __html: internalUrlTitle }} />
                  </p>
                  <WpButton isLink isDestructive onClick={handleRemoveLink}>
                    {__('Retirer le lien', 'amnesty')}
                  </WpButton>
                </div>
              )}
            </>
          )}

          {linkType === 'external' && (
            <>
              <TextControl
                label={__('URL externe', 'amnesty')}
                value={externalUrl}
                placeholder="https://exemple.com"
                onChange={(value) => setAttributes({ externalUrl: value })}
              />
              <TextControl
                label={__('Label du lien (optionnel)', 'amnesty')}
                value={externalLabel}
                placeholder={__('Texte à afficher', 'amnesty')}
                onChange={(value) => setAttributes({ externalLabel: value })}
                help={__("Si laissé vide, l'URL complète sera affichée.", 'amnesty')}
              />
            </>
          )}

          {(internalUrl || externalUrl) && (
            <ToggleControl
              label={__('Ouvrir dans un nouvel onglet', 'amnesty')}
              checked={!!targetBlank}
              onChange={(value) => setAttributes({ targetBlank: value })}
            />
          )}
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps({ className: 'read-also-block' })}>
        <p>
          {__('À lire aussi', 'amnesty')} : {linkContent}
        </p>
      </div>
    </>
  );
};

export default EditComponent;
