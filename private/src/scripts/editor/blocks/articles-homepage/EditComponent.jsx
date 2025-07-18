import ChipCategory from '../../components/ChipCategory.jsx';

const { __ } = wp.i18n;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl, Spinner } = wp.components;
const { useSelect } = wp.data;
const { useState, useEffect } = wp.element;
const apiFetch = wp.apiFetch;

const getCategoryLink = (slug) => {
  if (slug === 'landmark') {
    return '/reperes';
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
        placeholder={__('Tapez pour chercher un article&hellip;', 'amnesty')}
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
          {__('Article sélectionné :', 'amnesty')} <strong>{selectedPostTitle}</strong>
        </p>
      )}
    </div>
  );
};

const EditComponent = ({ attributes, setAttributes }) => {
  const { items = [] } = attributes;

  const blockProps = useBlockProps();

  const categories = useSelect(
    (select) => select('core').getEntityRecords('taxonomy', 'category', { per_page: 100 }),
    [],
  );

  const categoryOptions = categories
    ? [
        ...categories
          .filter((cat) => cat.name !== 'Non classé')
          .map((cat) => ({ label: cat.name, value: cat.slug })),
        { label: 'Repères', value: 'landmark' },
      ]
    : [];

  const updateItem = (index, key, value) => {
    const newItems = [...items];
    newItems[index] = { ...newItems[index], [key]: value };
    setAttributes({ items: newItems });
  };

  if (!categories) {
    return (
      <div {...blockProps}>
        <Spinner />
        <p>{__('Chargement des catégories…', 'amnesty')}</p>
      </div>
    );
  }

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
      default:
        return '';
    }
  };

  const formatDate = (dateString) => {
    if (!dateString) return '';
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('fr-FR', options);
  };

  return (
    <>
      <InspectorControls>
        {items.map((item, index) => (
          <PanelBody
            key={index}
            title={`${__('Bloc', 'amnesty')} ${index + 1}`}
            initialOpen={index === 0}
          >
            <SelectControl
              label={__('Type de contenu (catégorie)', 'amnesty')}
              value={item.category}
              options={categoryOptions}
              onChange={(value) => {
                const newItems = [...items];
                newItems[index] = {
                  ...newItems[index],
                  category: value,
                  selectedPostId: null,
                  selectedPostTitle: null,
                  selectedPostSlug: null,
                  _embedded: null,
                  selectedPostDate: null,
                  selectedPostCustomTerms: [],
                };
                setAttributes({ items: newItems });
              }}
            />

            {item.category ? (
              <PostSearchControl
                selectedPostId={item.selectedPostId}
                selectedPostTitle={item.selectedPostTitle}
                categorySlug={item.category}
                onChange={(postId, postTitle, postSlug, embedded, postDate, customTerms) => {
                  const newItems = [...items];
                  newItems[index] = {
                    ...newItems[index],
                    selectedPostId: postId,
                    selectedPostTitle: postTitle,
                    selectedPostSlug: postSlug,
                    _embedded: embedded || null,
                    selectedPostDate: postDate,
                    selectedPostCustomTerms: customTerms || [],
                  };
                  setAttributes({ items: newItems });
                }}
              />
            ) : (
              <p>{__('Sélectionnez une catégorie pour chercher des contenus.', 'amnesty')}</p>
            )}

            {index === 0 && (
              <TextControl
                label={__('Surtitre (facultatif)', 'amnesty')}
                value={item.subtitle || ''}
                onChange={(value) => updateItem(index, 'subtitle', value)}
              />
            )}
          </PanelBody>
        ))}
      </InspectorControls>

      <section {...blockProps} className="articles-homepage">
        <div className="articles-homepage-wrapper">
          <h2 className="title">À la une</h2>

          {items.length > 0 && (
            <div className="articles-homepage-container">
              {items[0].selectedPostTitle ? (
                <div className="article-main-desktop">
                  <ChipCategory item={items[0]} />
                  {items[0]._embedded?.['wp:featuredmedia']?.[0]?.source_url && (
                    <div className="article-image-container">
                      <a
                        href={`${getCategoryLink(items[0].category)}/${items[0].selectedPostSlug}`}
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        <img
                          className="article-image"
                          src={items[0]._embedded['wp:featuredmedia'][0].source_url}
                          alt={items[0].selectedPostTitle || ''}
                        />
                        <div className="article-content">
                          <div className="article-title-wrapper">
                            <h3
                              className="article-title"
                              dangerouslySetInnerHTML={{ __html: items[0].selectedPostTitle }}
                            />
                          </div>
                          {items[0].subtitle && (
                            <div className="article-subtitle-wrapper">
                              <p className="article-subtitle">{items[0].subtitle}</p>
                            </div>
                          )}
                          <div className="article-button-wrapper">
                            {items[0].selectedPostId && (
                              <div className="article-button">{__('Lire la suite', 'amnesty')}</div>
                            )}
                          </div>
                        </div>
                      </a>
                    </div>
                  )}
                  <div className="category-link">
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
                    <a
                      className="link"
                      target="_blank"
                      rel="noopener noreferrer"
                      href={getCategoryLink(items[0].category)}
                    >
                      {seeAll(items[0].category)}
                    </a>
                  </div>
                </div>
              ) : (
                <div className="article-main-desktop empty">
                  {__('Bloc 1 : Choisissez la catégorie et l&apos;article', 'amnesty')}{' '}
                </div>
              )}

              {items[0].selectedPostTitle ? (
                <div className="article-main-mobile">
                  <a
                    href={`${getCategoryLink(items[0].category)}/${items[0].selectedPostSlug}`}
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    <div className="wrapper">
                      <ChipCategory item={items[0]} />
                      <div className="article-main-mobile-image-wrapper">
                        {items[0]._embedded?.['wp:featuredmedia']?.[0]?.source_url && (
                          <img
                            className="article-main-mobile-image"
                            src={items[0]._embedded['wp:featuredmedia'][0].source_url}
                            alt={items[0].selectedPostTitle}
                          />
                        )}
                      </div>
                      <div className="article-main-mobile-content">
                        <div className="article-main-mobile-header">
                          {items[0].selectedPostDate && (
                            <p className="article-date">{formatDate(items[0].selectedPostDate)}</p>
                          )}
                          <h3
                            className="article-title"
                            dangerouslySetInnerHTML={{ __html: items[0].selectedPostTitle }}
                          />
                        </div>

                        {items[0].selectedPostCustomTerms &&
                          items[0].selectedPostCustomTerms.length > 0 && (
                            <div className="article-main-mobile-footer">
                              {items[0].selectedPostCustomTerms.map((term) => (
                                <span key={term.id} className={`term ${term.taxonomy}`}>
                                  {term.name}
                                </span>
                              ))}
                            </div>
                          )}
                      </div>
                    </div>
                  </a>
                  <div className="category-link">
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
                    <a
                      className="link"
                      target="_blank"
                      rel="noopener noreferrer"
                      href={getCategoryLink(items[0].category)}
                    >
                      {seeAll(items[0].category)}
                    </a>
                  </div>
                </div>
              ) : (
                <div className="article-main-mobile empty">
                  {__('Bloc 1 : Choisissez la catégorie et l&apos;article', 'amnesty')}{' '}
                </div>
              )}

              <div className="articles-side-column">
                {items.slice(1, 3).map((item, index) => (
                  <div key={index} className="article-side">
                    {item.selectedPostTitle ? (
                      <>
                        <a
                          href={`${getCategoryLink(item.category)}/${item.selectedPostSlug}`}
                          target="_blank"
                          rel="noopener noreferrer"
                        >
                          <div className="wrapper">
                            <ChipCategory item={item} />
                            <div className="article-side-image-wrapper">
                              {item._embedded?.['wp:featuredmedia']?.[0]?.source_url && (
                                <img
                                  className="article-side-image"
                                  src={item._embedded['wp:featuredmedia'][0].source_url}
                                  alt={item.selectedPostTitle || ''}
                                />
                              )}
                            </div>
                            <div className="article-side-content">
                              <div className="article-side-header">
                                {item.selectedPostDate && (
                                  <p className="article-date">
                                    {formatDate(item.selectedPostDate)}
                                  </p>
                                )}
                                <h3
                                  className="article-title"
                                  dangerouslySetInnerHTML={{
                                    __html:
                                      item.selectedPostTitle || __('Aucun contenu', 'amnesty'),
                                  }}
                                />
                              </div>

                              {item.selectedPostCustomTerms &&
                                item.selectedPostCustomTerms.length > 0 && (
                                  <div className="article-side-footer">
                                    {item.selectedPostCustomTerms.map((term) => (
                                      <span key={term.id} className={`term ${term.taxonomy}`}>
                                        {term.name}
                                      </span>
                                    ))}
                                  </div>
                                )}
                            </div>
                          </div>
                        </a>
                        <div className="category-link">
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
                          <a
                            className="link"
                            target="_blank"
                            rel="noopener noreferrer"
                            href={getCategoryLink(item.category)}
                          >
                            {seeAll(item.category)}
                          </a>
                        </div>
                      </>
                    ) : (
                      <div className="article-side empty">
                        {`Bloc ${index + 2} : ${__('Choisissez la catégorie et l&apos;article', 'amnesty')}`}{' '}
                      </div>
                    )}
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </section>
    </>
  );
};

export default EditComponent;
