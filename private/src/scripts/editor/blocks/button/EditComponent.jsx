import Button from './Button.jsx';

const { __ } = wp.i18n;
const { useEffect, useState } = wp.element;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl, Spinner, ToggleControl } = wp.components;
const { useSelect } = wp.data;
const apiFetch = wp.apiFetch;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const {
    label,
    size,
    style,
    icon,
    linkType,
    externalUrl,
    alignment,
    postType,
    internalUrl,
    internalUrlTitle,
    postId,
    targetBlank,
  } = attributes;

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
  const [searchTerm, setSearchTerm] = useState('');
  const [searchResults, setSearchResults] = useState([]);
  const [isSearching, setIsSearching] = useState(false);

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
        .catch(() => {
          return apiFetch({ path: `/wp/v2/pages/${postId}?_fields=link,title,type` });
        })
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
          console.warn(`Impossible de trouver le contenu pour le postId: ${postId}`);
        });
    }
  }, [postId, internalUrl]);

  useEffect(() => {
    if (!searchTerm || searchTerm.length < 2 || !postType) {
      setSearchResults([]);
      return;
    }
    const selectedTypeObject = availablePostTypes.find((type) => type.slug === postType);
    if (!selectedTypeObject) return;

    let path = `/wp/v2/${selectedTypeObject.rest_base}?search=${encodeURIComponent(searchTerm)}&per_page=10&_fields=id,title,link`;
    if (postType === 'post') {
      path += '&categories_exclude=1';
    }

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

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du lien', 'amnesty')} initialOpen={true}>
          <SelectControl
            label={__('Type de lien', 'amnesty')}
            value={linkType}
            options={[
              { label: 'Lien interne (contenu du site)', value: 'internal' },
              { label: 'Lien externe (URL)', value: 'external' },
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
                  <p style={{ margin: 0 }}>
                    {__('Lien sélectionné :', 'amnesty')}{' '}
                    <strong dangerouslySetInnerHTML={{ __html: internalUrlTitle }} />
                  </p>
                  <wp.components.Button isLink isDestructive onClick={handleRemoveLink}>
                    {__('Retirer le lien', 'amnesty')}
                  </wp.components.Button>
                </div>
              )}
            </>
          )}

          {linkType === 'external' && (
            <TextControl
              label={__('URL du lien externe', 'amnesty')}
              value={externalUrl}
              placeholder="https://exemple.com"
              onChange={(value) => setAttributes({ externalUrl: value })}
            />
          )}

          {(internalUrl || externalUrl) && (
            <ToggleControl
              label={__('Ouvrir dans un nouvel onglet', 'amnesty')}
              checked={!!targetBlank}
              onChange={(value) => setAttributes({ targetBlank: value })}
              help={
                targetBlank
                  ? __("Le lien s'ouvrira dans un nouvel onglet.", 'amnesty')
                  : __("Le lien s'ouvrira dans le même onglet.", 'amnesty')
              }
            />
          )}
        </PanelBody>

        <PanelBody title={__('Paramètres du bouton', 'amnesty')}>
          <TextControl
            label={__('Texte du bouton', 'amnesty')}
            value={label}
            placeholder={__('Ex: En savoir plus', 'amnesty')}
            onChange={(value) => setAttributes({ label: value })}
          />
          <SelectControl
            label={__('Taille', 'amnesty')}
            value={size}
            options={[
              { label: 'Petit', value: 'small' },
              { label: 'Moyen', value: 'medium' },
              { label: 'Grand', value: 'large' },
            ]}
            onChange={(value) => setAttributes({ size: value })}
          />
          <SelectControl
            label={__('Style', 'amnesty')}
            value={style}
            options={[
              { label: 'Contour Jaune', value: 'outline-yellow' },
              { label: 'Contour Noir', value: 'outline-black' },
              { label: 'Sans Contour', value: 'no-outline' },
              { label: 'Fond Noir', value: 'bg-black' },
              { label: 'Fond Jaune', value: 'bg-yellow' },
            ]}
            onChange={(value) => setAttributes({ style: value })}
          />
          <SelectControl
            label={__('Icône', 'amnesty')}
            value={icon}
            options={[
              { label: 'Aucune', value: '' },
              { label: 'Flèche gauche', value: 'arrow-left' },
              { label: 'Flèche droite', value: 'arrow-right' },
              { label: 'Loupe +', value: 'zoom-in' },
            ]}
            onChange={(value) => setAttributes({ icon: value })}
          />
          <SelectControl
            label={__('Alignement', 'amnesty')}
            value={alignment}
            options={[
              { label: 'Gauche', value: 'left' },
              { label: 'Centre', value: 'center' },
              { label: 'Droite', value: 'right' },
            ]}
            onChange={(value) => setAttributes({ alignment: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()}>
        <Button
          label={label}
          size={size}
          style={style}
          icon={icon}
          link={linkType === 'external' ? externalUrl : internalUrl || '#'}
          alignment={alignment}
        />
      </div>
    </>
  );
};

export default EditComponent;
