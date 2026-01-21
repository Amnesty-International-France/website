const { __ } = wp.i18n;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, Button, Notice, SelectControl, Spinner, TextControl } = wp.components;
const { useState, useEffect } = wp.element;
const apiFetch = wp.apiFetch;

const PetitionSearchControl = ({ petitionType, onSelectPost, excludeIds = [], disabled }) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!searchTerm || searchTerm.length < 3 || disabled) {
      setResults([]);
      setLoading(false);
      return;
    }

    setLoading(true);
    apiFetch({
      path: `/wp/v2/petition?search=${encodeURIComponent(searchTerm)}&per_page=20&_embed`,
    })
      .then((posts) => {
        const filtered = posts.filter((post) => {
          if (excludeIds.includes(post.id)) {
            return false;
          }
          if (!petitionType) {
            return true;
          }
          const typeValue = post?.meta?.type || post?.acf?.type?.value;
          return typeValue === petitionType;
        });
        setResults(filtered);
        setLoading(false);
      })
      .catch(() => {
        setResults([]);
        setLoading(false);
      });
  }, [searchTerm, petitionType, excludeIds, disabled]);

  return (
    <div>
      <TextControl
        label={__('Ajouter une pétition', 'amnesty')}
        value={searchTerm}
        onChange={setSearchTerm}
        placeholder={__('Tapez pour chercher', 'amnesty')}
        disabled={disabled}
      />
      {loading && <Spinner />}
      {!loading && results.length > 0 && (
        <ul className="multi-post-search-results">
          {results.map((post) => (
            <li
              key={post.id}
              onClick={() => {
                onSelectPost(post);
                setSearchTerm('');
                setResults([]);
              }}
            >
              <strong dangerouslySetInnerHTML={{ __html: post.title.rendered }} />
            </li>
          ))}
        </ul>
      )}
    </div>
  );
};

const EditComponent = ({ attributes, setAttributes }) => {
  const { petitionType, selectedPosts } = attributes;
  const blockProps = useBlockProps({ className: 'changez-leur-histoire-slider-editor-preview' });
  const [errorMessage, setErrorMessage] = useState(null);

  useEffect(() => {
    if (selectedPosts.length > 20) {
      setErrorMessage(__('Vous pouvez sélectionner au maximum 20 pétitions.', 'amnesty'));
    } else if (selectedPosts.length > 0 && selectedPosts.length < 4) {
      setErrorMessage(__('Veuillez sélectionner au moins 4 pétitions.', 'amnesty'));
    } else {
      setErrorMessage(null);
    }
  }, [selectedPosts]);

  const onTypeChange = (newType) => {
    setAttributes({
      petitionType: newType,
    });
  };

  const handleSelectPost = (post) => {
    if (selectedPosts.find((p) => p.id === post.id)) {
      return;
    }
    if (selectedPosts.length >= 20) {
      return;
    }
    const newPost = {
      id: post.id,
      title: post.title.rendered,
      link: post.link,
      featured_media_url: post._embedded?.['wp:featuredmedia']?.[0]?.source_url || null,
    };
    setAttributes({ selectedPosts: [...selectedPosts, newPost] });
  };

  const handleRemovePost = (postId) => {
    const newSelectedPosts = selectedPosts.filter((post) => post.id !== postId);
    setAttributes({ selectedPosts: newSelectedPosts });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Configuration du slider', 'amnesty')} initialOpen={true}>
          <SelectControl
            label={__('Type de pétition', 'amnesty')}
            value={petitionType}
            options={[
              { label: __('Sélectionnez un type', 'amnesty'), value: '' },
              { label: __('Pétition', 'amnesty'), value: 'petition' },
              { label: __('Action de soutien', 'amnesty'), value: 'action-soutien' },
            ]}
            onChange={onTypeChange}
          />
          <PetitionSearchControl
            petitionType={petitionType}
            onSelectPost={handleSelectPost}
            excludeIds={selectedPosts.map((p) => p.id)}
            disabled={selectedPosts.length >= 20}
          />
          {selectedPosts.length > 0 && (
            <div className="selected-posts-list">
              <h4>
                {__('Pétitions sélectionnées', 'amnesty')} ({selectedPosts.length})
              </h4>
              <ul>
                {selectedPosts.map((post) => (
                  <li key={post.id}>
                    <span dangerouslySetInnerHTML={{ __html: post.title }} />
                    <Button isLink isDestructive onClick={() => handleRemovePost(post.id)}>
                      {__('Retirer', 'amnesty')}
                    </Button>
                  </li>
                ))}
              </ul>
            </div>
          )}
          {errorMessage && (
            <Notice status="error" isDismissible={false}>
              {errorMessage}
            </Notice>
          )}
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        {selectedPosts.length > 0 ? (
          <div className="editor-preview-grid">
            {selectedPosts.map((post) => (
              <div key={post.id} className="editor-preview-card">
                {post.featured_media_url ? (
                  <img src={post.featured_media_url} alt="" />
                ) : (
                  <div className="editor-preview-card-placeholder">
                    <span>{__('Image non disponible', 'amnesty')}</span>
                  </div>
                )}
                <p
                  className="editor-preview-card-title"
                  dangerouslySetInnerHTML={{ __html: post.title }}
                />
              </div>
            ))}
          </div>
        ) : (
          <div className="editor-preview-placeholder">
            <p>
              {__(
                'Choisissez un type de pétition puis sélectionnez au moins 4 éléments dans le panneau de configuration.',
                'amnesty',
              )}
            </p>
          </div>
        )}
      </div>
    </>
  );
};

export default EditComponent;
