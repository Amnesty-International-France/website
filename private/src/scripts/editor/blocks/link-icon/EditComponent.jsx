import classnames from 'classnames';
import CustomButton from '../button/Button.jsx';
import Icon from '../../components/Icon.jsx';

const { useEffect, useState } = wp.element;
const { __ } = wp.i18n;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl, Spinner } = wp.components;
const { useSelect } = wp.data;
const apiFetch = wp.apiFetch;

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

    let path = '';
    if (categorySlug === 'landmark') {
      path = `/wp/v2/landmark?search=${encodeURIComponent(searchTerm)}&per_page=10&_embed`;
    } else {
      const categoryObj = categories?.find((cat) => cat.slug === categorySlug);
      if (!categoryObj) {
        setResults([]);
        setLoading(false);
        return;
      }
      path = `/wp/v2/posts?search=${encodeURIComponent(searchTerm)}&category=${categoryObj.id}&per_page=10&_embed`;
    }

    apiFetch({ path })
      .then((posts) => {
        setResults(posts);
        setLoading(false);
      })
      .catch((error) => {
        console.error('Error fetching posts:', error);
        setResults([]);
        setLoading(false);
      });
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
        label={__('Rechercher un contenu', 'amnesty')}
        value={searchTerm}
        onChange={setSearchTerm}
        placeholder={__('Tapez pour chercher un article ou une page&hellip;', 'amnesty')}
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
            const postLink = post.link;
            const postTitle = post.title.rendered;

            const featuredImageUrl = post._embedded?.['wp:featuredmedia']?.[0]?.source_url || '';
            const allExtractedTerms = extractAllCustomTerms(post._embedded);

            return (
              <li
                key={post.id}
                style={{
                  cursor: 'pointer',
                  padding: '8px 10px',
                  backgroundColor: postLink === selectedPostId ? '#e0f2f7' : 'transparent',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.75rem',
                  borderBottom: '1px solid #eee',
                  transition: 'background-color 0.2s ease-in-out',
                }}
                onClick={() => {
                  onChange(postLink, postTitle);
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
      {!selectedPostTitle && !searchTerm && !loading && (
        <p>{__('Aucun contenu sélectionné.', 'amnesty')}</p>
      )}
    </div>
  );
};

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const {
    title,
    titleSize,
    description,
    icon,
    bgColor,
    buttonLink,
    selectedCategory,
    selectedPostTitle,
  } = attributes;

  const reqSvgs = require.context('../../icons', false, /\.svg$/);

  const iconOptions = reqSvgs.keys().map((filePath) => {
    const fileName = filePath.replace('./', '').replace('.svg', '');
    return {
      label: fileName,
      value: fileName,
    };
  });

  const categories = useSelect(
    (select) => select('core').getEntityRecords('taxonomy', 'category', { per_page: 100 }),
    [],
  );

  const categoryOptions = categories
    ? [
        { label: __('Choisir une catégorie', 'amnesty'), value: '' },
        ...categories
          .filter((cat) => cat.name !== 'Non classé')
          .map((cat) => ({ label: cat.name, value: cat.slug })),
        { label: __('Repères', 'amnesty'), value: 'landmark' },
      ]
    : [];

  const handlePostSelect = (link, postTitle) => {
    setAttributes({
      buttonLink: link,
      selectedPostTitle: postTitle,
    });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres', 'amnesty')}>
          <TextControl
            label={__('Titre', 'amnesty')}
            value={title}
            onChange={(value) => setAttributes({ title: value })}
          />
          <TextControl
            label={__('Description', 'amnesty')}
            value={description}
            onChange={(value) => setAttributes({ description: value })}
          />
          <SelectControl
            label={__('Icône', 'amnesty')}
            value={icon}
            options={iconOptions}
            onChange={(value) => setAttributes({ icon: value })}
          />
          <SelectControl
            label={__('Filtrer par catégorie', 'amnesty')}
            value={selectedCategory || ''}
            options={categoryOptions}
            onChange={(value) => {
              setAttributes({
                selectedCategory: value,
                buttonLink: '',
                selectedPostTitle: '',
              });
            }}
          />
          {selectedCategory ? (
            <PostSearchControl
              selectedPostId={buttonLink}
              selectedPostTitle={selectedPostTitle}
              categorySlug={selectedCategory}
              onChange={handlePostSelect}
            />
          ) : (
            <p>
              {__('Sélectionnez une catégorie pour afficher les contenus disponibles.', 'amnesty')}
            </p>
          )}
        </PanelBody>
        <PanelBody title={__('Styles', 'amnesty')}>
          <SelectControl
            label={__('Taille du titre', 'amnesty')}
            value={titleSize}
            options={[
              { label: __('Petit', 'amnesty'), value: 'small' },
              { label: __('Moyen', 'amnesty'), value: 'medium' },
              { label: __('Grand', 'amnesty'), value: 'large' },
            ]}
            onChange={(value) => setAttributes({ titleSize: value })}
          />
          <SelectControl
            label={__('Couleur de fond', 'amnesty')}
            value={bgColor}
            options={[
              { label: __('Blanc', 'amnesty'), value: 'white' },
              { label: __('Gris', 'amnesty'), value: 'grey' },
              { label: __('Noir', 'amnesty'), value: 'black' },
            ]}
            onChange={(value) => setAttributes({ bgColor: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className={classnames('link-icon-block', bgColor)}>
        <p className={classnames('title', titleSize)}>{title}</p>
        {icon && (
          <div className="container-icon">
            <Icon name={icon} colorClass={bgColor === 'black' ? 'primary' : 'black'} />
          </div>
        )}
        <p className="description">{description}</p>
        <CustomButton
          label="En savoir plus"
          size="medium"
          link={buttonLink}
          alignment="center"
          style="bg-yellow"
        />
      </div>
    </>
  );
};

export default EditComponent;
