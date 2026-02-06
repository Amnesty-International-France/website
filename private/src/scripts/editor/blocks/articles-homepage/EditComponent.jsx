import ChipCategory from '../../components/ChipCategory.jsx';
import PostSearchControl from '../../components/PostSearchControl.jsx';

const { useSelect } = wp.data;
const { __ } = wp.i18n;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl } = wp.components;

const getCategoryLink = (slug) => {
  if (slug === 'landmark') {
    return '/reperes';
  }

  if (slug === 'actualities-my-space') {
    return '/mon-espace/actualites';
  }

  return `/${slug}`;
};

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

const EditComponent = ({ attributes, setAttributes }) => {
  const { items = [] } = attributes;
  const blockProps = useBlockProps();
  const isMySpace = useSelect(
    (select) => select('core/editor').getEditedPostAttribute('slug') === 'mon-espace',
    [],
  );
  const allowedTypesForThisBlock = isMySpace
    ? ['actualities-my-space']
    : [
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

  const updateItem = (index, key, value) => {
    const newItems = [...items];
    newItems[index] = { ...newItems[index], [key]: value };
    setAttributes({ items: newItems });
  };

  const handlePostSelection = (index, post) => {
    const newItems = [...items];

    if (post) {
      const allExtractedTerms = extractAllCustomTerms(post._embedded);
      const primaryCategory = post._embedded?.['wp:term']?.[0]?.find(
        (term) => term.taxonomy === 'category',
      );

      const categoryMap = {
        landmark: 'landmark',
        'actualities-my-space': 'actualities-my-space',
      };

      const category = categoryMap[post.type] || primaryCategory?.slug || '';

      newItems[index] = {
        ...newItems[index],
        selectedPostId: post.id,
        selectedPostTitle: post.title.rendered,
        selectedPostSlug: post.slug,
        _embedded: post._embedded || null,
        selectedPostDate: post.date,
        selectedPostCustomTerms: allExtractedTerms || [],
        category,
      };
    } else {
      newItems[index] = {
        subtitle: newItems[index].subtitle,
      };
    }

    setAttributes({ items: newItems });
  };

  const seeAll = (categorySlug) => {
    switch (categorySlug) {
      case 'actualites':
      case 'actualities-my-space':
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
            <PostSearchControl
              allowedTypes={allowedTypesForThisBlock}
              onPostSelect={(post) => handlePostSelection(index, post)}
            />
            {item.selectedPostTitle && (
              <p style={{ fontStyle: 'italic', marginTop: '1rem' }}>
                {__('Article sélectionné :', 'amnesty')}{' '}
                <strong dangerouslySetInnerHTML={{ __html: item.selectedPostTitle }} />
              </p>
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
                  {__('Bloc 1 : Choisissez un contenu dans le panneau de droite', 'amnesty')}{' '}
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
                  {__('Bloc 1 : Choisissez un contenu dans le panneau de droite', 'amnesty')}{' '}
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
                        {`Bloc ${index + 2} : ${__('Choisissez un contenu dans le panneau de droite', 'amnesty')}`}{' '}
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
