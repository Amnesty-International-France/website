import classnames from 'classnames';

const { __ } = wp.i18n;
const { useEffect, useState } = wp.element;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, TextControl, SelectControl, ToggleControl, TextareaControl, Spinner, Button } =
  wp.components;
const { useSelect } = wp.data;
const apiFetch = wp.apiFetch;

const getCategoryLink = (slug) => {
  if (slug === 'landmark') {
    return '/reperes';
  }
  if (slug === 'page') {
    return '';
  }
  return `/${slug}`;
};

const PostSearchControl = ({ selectedPostId, selectedPostTitle, categorySlug, onChange }) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);

  const categories = useSelect(
    (select) => select('core').getEntityRecords('taxonomy', 'category', { per_page: 100 }),
    [],
  );

  useEffect(() => {
    if (!searchTerm || !categorySlug) {
      setResults([]);
      return;
    }

    setLoading(true);

    if (categorySlug === 'landmark') {
      apiFetch({
        path: `/wp/v2/landmark?search=${encodeURIComponent(searchTerm)}&per_page=10&_embed`,
      })
        .then((posts) => {
          setResults(posts);
          setLoading(false);
        })
        .catch(() => {
          setResults([]);
          setLoading(false);
        });
    } else if (categorySlug === 'page') {
      apiFetch({
        path: `/wp/v2/pages?search=${encodeURIComponent(searchTerm)}&per_page=10&_embed`,
      })
        .then((pages) => {
          setResults(pages);
          setLoading(false);
        })
        .catch(() => {
          setResults([]);
          setLoading(false);
        });
    } else {
      const categoryObj = categories?.find((cat) => cat.slug === categorySlug);
      if (!categoryObj) {
        setResults([]);
        setLoading(false);
        return;
      }

      apiFetch({
        path: `/wp/v2/posts?search=${encodeURIComponent(searchTerm)}&category=${categoryObj.id}&per_page=10&_embed`,
      })
        .then((posts) => {
          setResults(posts);
          setLoading(false);
        })
        .catch(() => {
          setResults([]);
          setLoading(false);
        });
    }
  }, [searchTerm, categorySlug, categories]);

  const extractAllCustomTerms = (embeddedData) => {
    if (!embeddedData || !Array.isArray(embeddedData['wp:term'])) {
      return [];
    }

    let allCustomTerms = [];
    embeddedData['wp:term'].forEach((termGroup) => {
      if (Array.isArray(termGroup)) {
        const customTermsInGroup = termGroup.filter(
          (term) => term.taxonomy !== 'category' && term.taxonomy !== 'post_tag',
        );
        allCustomTerms = allCustomTerms.concat(
          customTermsInGroup.map(({ id, name, slug, taxonomy }) => ({ id, name, slug, taxonomy })),
        );
      }
    });
    return allCustomTerms;
  };

  return (
    <div>
      <TextControl
        label={__('Sélectionner un contenu spécifique (facultatif)', 'amnesty')}
        value={searchTerm}
        onChange={setSearchTerm}
        placeholder={__('Tapez pour chercher un contenu', 'amnesty')}
      />

      {loading && <Spinner />}

      {!loading && results.length > 0 && (
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
          {results.map((post) => {
            const featuredImageUrl = post._embedded?.['wp:featuredmedia']?.[0]?.source_url || '';
            const allExtractedTerms = extractAllCustomTerms(post._embedded);

            let postCategoryName = '';
            if (categorySlug === 'landmark') {
              postCategoryName = 'Repères';
            } else if (categorySlug === 'page') {
              postCategoryName = 'Page';
            } else {
              const postCategory = post._embedded?.['wp:term']?.[0]?.find(
                (term) => term.taxonomy === 'category',
              );
              postCategoryName = postCategory?.name || '';
            }

            return (
              <li
                key={post.id}
                style={{
                  cursor: 'pointer',
                  padding: '8px 10px',
                  backgroundColor: post.id === selectedPostId ? '#e0f2f7' : 'transparent',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.75rem',
                  borderBottom: '1px solid #eee',
                  transition: 'background-color 0.2s ease-in-out',
                }}
                onClick={() => {
                  onChange(
                    post.id,
                    post.title.rendered,
                    post.slug,
                    post._embedded,
                    post.date,
                    allExtractedTerms,
                    postCategoryName,
                    categorySlug,
                  );
                  setSearchTerm('');
                  setResults([]);
                }}
              >
                {featuredImageUrl && (
                  <img
                    src={featuredImageUrl}
                    alt={post.title.rendered}
                    style={{
                      width: 50,
                      height: 50,
                      objectFit: 'cover',
                      borderRadius: 4,
                      flexShrink: 0,
                    }}
                  />
                )}
                <div style={{ flexGrow: 1 }}>
                  <strong dangerouslySetInnerHTML={{ __html: post.title.rendered }} />
                  <div style={{ fontSize: '0.85em', color: '#666', marginTop: '4px' }}>
                    {allExtractedTerms.length > 0 && (
                      <span style={{ marginRight: '8px' }}>
                        {allExtractedTerms.map((term) => term.name).join(', ')}
                      </span>
                    )}
                    {post._embedded?.['wp:term']?.[0]?.[0]?.name &&
                      post._embedded['wp:term'][0][0].taxonomy === 'category' && (
                        <span
                          style={{
                            marginLeft: allExtractedTerms.length > 0 ? '0' : '0',
                            marginRight: '8px',
                          }}
                        >
                          {allExtractedTerms.length > 0 ? '| ' : ''}
                          {post._embedded['wp:term'][0][0].name}
                        </span>
                      )}
                  </div>
                </div>
              </li>
            );
          })}
        </ul>
      )}

      {selectedPostTitle && (
        <p>
          {__('Contenu sélectionné :', 'amnesty')} <strong>{selectedPostTitle}</strong>
        </p>
      )}
    </div>
  );
};

const EditComponent = ({ attributes, setAttributes }) => {
  const {
    custom,
    direction,
    postId,
    title,
    subtitle,
    category,
    permalink,
    thumbnail,
    text,
    selectedPostCategorySlug,
  } = attributes;

  const blockProps = useBlockProps();

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
        { label: 'Pages', value: 'page' },
        { label: 'Repères', value: 'landmark' },
      ]
    : [];

  const getCategoryNameFromSlug = (slug) => {
    if (slug === 'landmark') {
      return 'Repères';
    }
    if (slug === 'page') {
      return 'Pages';
    }
    const selectedCat = categories?.find((cat) => cat.slug === slug);
    return selectedCat ? selectedCat.name : '';
  };

  const selectedMedia = useSelect(
    (select) => (thumbnail ? select('core').getMedia(thumbnail) : null),
    [thumbnail],
  );

  const handleSelectImage = (newMedia) => {
    setAttributes({ thumbnail: newMedia.id });
  };

  const updateCustom = (value) => {
    setAttributes({ custom: value });
    if (value) {
      setAttributes({
        postId: null,
        title: '',
        subtitle: '',
        category: '',
        permalink: '',
        thumbnail: null,
        text: '',
        selectedPostCategorySlug: '',
        selectedPostDate: '',
        selectedPostCustomTerms: [],
      });
    } else {
      setAttributes({
        title: '',
        subtitle: '',
        category: '',
        permalink: '',
        thumbnail: null,
        text: '',
      });
    }
  };

  const updateDirection = (value) => {
    setAttributes({ direction: value });
  };

  const updateTitle = (newTitle) => {
    setAttributes({ title: newTitle });
  };

  const updateSubtitle = (newSubtitle) => {
    setAttributes({ subtitle: newSubtitle });
  };

  const updateCategory = (newCategory) => {
    setAttributes({ category: newCategory });
  };

  const updatePermalink = (newPermalink) => {
    setAttributes({ permalink: newPermalink });
  };

  const updateText = (newText) => {
    setAttributes({ text: newText });
  };

  const handlePostSearchControlChange = (
    newPostId,
    postTitle,
    postSlug,
    embedded,
    postDate,
    customTerms,
    postCategoryName,
    selectedCategorySlugFromDropdown,
  ) => {
    setAttributes({
      postId: newPostId,
      title: postTitle,
      subtitle: embedded?.excerpt?.rendered || '',
      category: postCategoryName,
      permalink: `${getCategoryLink(selectedCategorySlugFromDropdown)}/${postSlug}`,
      thumbnail: embedded?.['wp:featuredmedia']?.[0]?.id || null,
      text: embedded?.excerpt?.rendered || '',
      selectedPostCategorySlug: selectedCategorySlugFromDropdown,
      selectedPostDate: postDate,
      selectedPostCustomTerms: customTerms,
    });
  };

  if (!categories) {
    return (
      <div {...blockProps}>
        <Spinner />
        <p>{__('Chargement…', 'amnesty')}</p>
      </div>
    );
  }

  const linkProps = {
    href: permalink,
    className: 'card-image-text-block-link',
  };

  if (custom) {
    linkProps.target = '_blank';
    linkProps.rel = 'noopener noreferrer';
  }

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du bloc', 'amnesty')} initialOpen={true}>
          <ToggleControl
            __nextHasNoMarginBottom
            label={__('Contenu personnalisé', 'amnesty')}
            checked={custom}
            onChange={updateCustom}
          />
          <SelectControl
            label={__('Disposition', 'amnesty')}
            value={direction}
            options={[
              { label: __('Horizontal', 'amnesty'), value: 'horizontal' },
              { label: __('Vertical', 'amnesty'), value: 'vertical' },
            ]}
            onChange={updateDirection}
          />
          {!custom && (
            <>
              <SelectControl
                label={__('Type de contenu', 'amnesty')}
                value={selectedPostCategorySlug}
                options={categoryOptions}
                onChange={(value) => {
                  const newCategoryName = getCategoryNameFromSlug(value);
                  setAttributes({
                    selectedPostCategorySlug: value,
                    category: newCategoryName,
                    postId: null,
                    title: '',
                    subtitle: '',
                    permalink: '',
                    thumbnail: null,
                    text: '',
                  });
                }}
              />
              {selectedPostCategorySlug ? (
                <PostSearchControl
                  selectedPostId={postId}
                  selectedPostTitle={title}
                  categorySlug={selectedPostCategorySlug}
                  onChange={handlePostSearchControlChange}
                />
              ) : (
                <p>{__('Sélectionnez un type pour chercher des contenus.', 'amnesty')}</p>
              )}
            </>
          )}
          {custom && (
            <>
              <MediaUploadCheck>
                <MediaUpload
                  onSelect={handleSelectImage}
                  allowedTypes={['image']}
                  value={thumbnail}
                  render={({ open }) => (
                    <Button onClick={open} isPrimary>
                      {thumbnail
                        ? __('Changer l’image', 'amnesty')
                        : __('Ajouter une image', 'amnesty')}
                    </Button>
                  )}
                />
              </MediaUploadCheck>
              <TextControl
                label={__('Catégorie', 'amnesty')}
                value={category}
                onChange={updateCategory}
                placeholder={__('Entrez une catégorie…', 'amnesty')}
              />
              <TextControl
                label={__('Titre', 'amnesty')}
                value={title}
                onChange={updateTitle}
                placeholder={__('Entrez un titre…', 'amnesty')}
              />
              <TextControl
                label={__('Sous-titre', 'amnesty')}
                value={subtitle}
                onChange={updateSubtitle}
                placeholder={__('Entrez un sous-titre…', 'amnesty')}
              />
              <TextControl
                label={__('Lien', 'amnesty')}
                value={permalink}
                onChange={updatePermalink}
                placeholder={__('Lien', 'amnesty')}
              />
              <TextareaControl
                __nextHasNoMarginBottom
                label={__('Texte', 'amnesty')}
                value={text}
                onChange={updateText}
                placeholder={__('Texte', 'amnesty')}
              />
            </>
          )}
        </PanelBody>
      </InspectorControls>

      <div {...blockProps} className={classnames('card-image-text-block', direction)}>
        <p className="card-image-text-category">{category}</p>
        <div className="card-content-wrapper">
          <a {...linkProps}>
            <div className="card-image-text-thumbnail-wrapper">
              {selectedMedia && (
                <img className="card-image-text-thumbnail" src={selectedMedia.source_url} alt="" />
              )}
            </div>
            <div className="card-image-text-content-container">
              <div className="card-image-text-content">
                <p className="card-image-text-content-subtitle">{subtitle}</p>
                <p className="card-image-text-content-title">{title}</p>
                <p className="card-image-text-content-text">{text}</p>
                <div className="card-image-text-content-see-more">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    viewBox="0 0 16 16"
                    fill="none"
                  >
                    <path
                      fillRule="evenodd"
                      clipRule="evenodd"
                      d="M10.7826 7.33336L7.20663 3.75736L8.14930 2.81470L13.3346 8.00003L8.14930 13.1854L7.20663 12.2427L10.7826 8.66670H2.66797V7.33336H10.7826Z"
                      fill="black"
                    />
                  </svg>
                  <p className="card-image-text-content-see-more-label">Voir la suite</p>
                </div>
              </div>
            </div>
          </a>
        </div>
      </div>
    </>
  );
};

export default EditComponent;
