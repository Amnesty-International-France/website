const { __ } = wp.i18n;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, Button, Notice, SelectControl, Spinner, TextControl } = wp.components;
const { useSelect } = wp.data;
const { useState, useEffect } = wp.element;
const apiFetch = wp.apiFetch;

const seeAll = (categorySlug) => {
  switch (categorySlug) {
    case 'actualites':
      return 'Voir toutes les actualités';
    case 'campagnes':
      return 'Voir toutes les campagnes';
    case 'chroniques':
      return 'Voir tous les articles la chronique';
    case 'dossiers':
      return 'Voir tous les dossiers';
    case 'landmark':
      return 'Voir tous les repères';
    case 'training':
      return 'Voir toutes les formations';
    case 'edh':
      return 'Voir toutes les ressources pédagogiques';
    case 'petition':
      return 'Voir toutes les pétitions';
    case 'document':
      return 'Voir tous les documents';
    default:
      return '';
  }
};

const MultiPostSearchControl = ({ categorySlug, onSelectPost, excludeIds = [] }) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);

  const categories = useSelect(
    (select) => select('core').getEntityRecords('taxonomy', 'category', { per_page: 100 }),
    [],
  );

  useEffect(() => {
    if (!searchTerm || searchTerm.length < 3 || !categorySlug) {
      setResults([]);
      return;
    }
    const fetchPosts = (path) => {
      apiFetch({ path })
        .then((posts) => {
          setResults(posts);
          setLoading(false);
        })
        .catch(() => {
          setResults([]);
          setLoading(false);
        });
    };
    setLoading(true);
    if (categorySlug === 'landmark') {
      fetchPosts(`/wp/v2/landmark?search=${encodeURIComponent(searchTerm)}&per_page=10&_embed`);
    } else if (categorySlug === 'training') {
      fetchPosts(`/wp/v2/training?search=${encodeURIComponent(searchTerm)}&per_page=10&_embed`);
    } else if (categorySlug === 'edh') {
      fetchPosts(`/wp/v2/edh?search=${encodeURIComponent(searchTerm)}&per_page=10&_embed`);
    } else if (categorySlug === 'petition') {
      fetchPosts(`/wp/v2/petition?search=${encodeURIComponent(searchTerm)}&per_page=10&_embed`);
    } else if (categorySlug === 'document') {
      fetchPosts(`/wp/v2/document?search=${encodeURIComponent(searchTerm)}&per_page=10&_embed`);
    } else {
      const categoryObj = categories?.find((cat) => cat.slug === categorySlug);
      if (!categoryObj) {
        setResults([]);
        setLoading(false);
        return;
      }
      fetchPosts(
        `/wp/v2/posts?search=${encodeURIComponent(searchTerm)}&category=${categoryObj.id}&per_page=10&_embed`,
      );
    }
  }, [searchTerm, categorySlug, categories]);

  const filteredResults = results.filter((post) => !excludeIds.includes(post.id));
  return (
    <div>
      <TextControl
        label={__('Ajouter un contenu', 'amnesty')}
        value={searchTerm}
        onChange={setSearchTerm}
        placeholder={__('Tapez pour chercher', 'amnesty')}
      />
      {loading && <Spinner />}
      {!loading && filteredResults.length > 0 && (
        <ul className="multi-post-search-results">
          {filteredResults.map((post) => (
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
  const { postType, selectedPosts } = attributes;
  const blockProps = useBlockProps({ className: 'slider-block-editor-preview' });
  const [errorMessage, setErrorMessage] = useState(null);
  const categories = useSelect(
    (select) => select('core').getEntityRecords('taxonomy', 'category', { per_page: 100 }),
    [],
  );
  const categoryOptions = categories
    ? [
        { label: __('Sélectionnez un type', 'amnesty'), value: '' },
        ...categories
          .filter((cat) => cat.name !== 'Non classé')
          .map((cat) => ({ label: cat.name, value: cat.slug })),
        { label: 'Repères', value: 'landmark' },
        { label: 'Formations', value: 'training' },
        { label: 'Ressources pédagogiques', value: 'edh' },
        { label: 'Pétitions', value: 'petition' },
        { label: 'Documents', value: 'document' },
      ]
    : [{ label: __('Chargement', 'amnesty'), value: '', disabled: true }];

  useEffect(() => {
    if (selectedPosts.length > 0 && selectedPosts.length < 4) {
      setErrorMessage(__('Veuillez sélectionner au moins 4 articles.', 'amnesty'));
    } else {
      setErrorMessage(null);
    }
  }, [selectedPosts]);

  const onPostTypeChange = (newPostType) => {
    setAttributes({
      postType: newPostType,
      selectedPosts: [],
    });
  };

  const handleSelectPost = (post) => {
    if (selectedPosts.find((p) => p.id === post.id)) {
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

  const SvgIconLink = () => (
    <svg
      className="icon"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      strokeWidth="1.5"
      stroke="currentColor"
    >
      <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
    </svg>
  );

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Configuration du Slider', 'amnesty')} initialOpen={true}>
          <SelectControl
            label={__('Type de contenu', 'amnesty')}
            value={postType}
            options={categoryOptions}
            onChange={onPostTypeChange}
          />
          {postType && (
            <MultiPostSearchControl
              categorySlug={postType}
              onSelectPost={handleSelectPost}
              excludeIds={selectedPosts.map((p) => p.id)}
            />
          )}
          {selectedPosts.length > 0 && (
            <div className="selected-posts-list">
              <h4>
                {__('Articles sélectionnés', 'amnesty')} ({selectedPosts.length})
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
        {postType && postType !== 'document' && (
          <div className="category-link">
            <div className="icon-container">
              <SvgIconLink />
            </div>
            <a className="cat-link" href="#!">
              {seeAll(postType)}
            </a>
          </div>
        )}
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
                'Veuillez sélectionner au moins 4 articles dans le panneau de configuration pour afficher la prévisualisation.',
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
