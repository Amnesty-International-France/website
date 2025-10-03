const { useEffect, useState, useMemo } = wp.element;
const { __ } = wp.i18n;
const { TextControl, SelectControl, Spinner } = wp.components;
const { useSelect } = wp.data;
const apiFetch = wp.apiFetch;

const PostSearchControl = ({ onPostSelect, allowedTypes = [] }) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);
  const [selection, setSelection] = useState('');

  const { postCategories, postTypes } = useSelect(
    (select) => {
      const { getEntityRecords, getPostTypes } = select('core');
      return {
        postCategories: allowedTypes.includes('post')
          ? getEntityRecords('taxonomy', 'category', { per_page: 100 })
          : [],
        postTypes: getPostTypes({ per_page: -1 }),
      };
    },
    [allowedTypes],
  );

  const masterOptions = useMemo(() => {
    if (!postTypes) return [];

    let options = [{ label: __('Choisir un type de contenu', 'amnesty'), value: '' }];

    if (allowedTypes.includes('post') && postCategories) {
      const categoryOptions = postCategories
        .filter((cat) => cat.name !== 'Non classé')
        .map((cat) => ({
          label: `Catégorie : ${cat.name}`,
          value: `cat:${cat.slug}`,
        }));
      options = [...options, ...categoryOptions];
    }

    const otherTypeOptions = allowedTypes
      .filter((type) => type !== 'post')
      .map((slug) => {
        const postTypeObject = postTypes.find((pt) => pt.slug === slug);
        return {
          label: postTypeObject ? postTypeObject.name : slug,
          value: `type:${slug}`,
        };
      });

    return [...options, ...otherTypeOptions];
  }, [allowedTypes, postCategories, postTypes]);

  useEffect(() => {
    if (!searchTerm || !selection) {
      setResults([]);
      return;
    }
    setLoading(true);

    let path = '';
    const [searchType, slug] = selection.split(':');

    if (searchType === 'cat') {
      const categoryObj = postCategories?.find((cat) => cat.slug === slug);
      if (categoryObj) {
        path = `/wp/v2/posts?search=${encodeURIComponent(searchTerm)}&category=${categoryObj.id}&per_page=10&_embed`;
      }
    } else if (searchType === 'type') {
      const endpoint = slug;
      path = `/wp/v2/${endpoint}?search=${encodeURIComponent(searchTerm)}&per_page=10&_embed`;
    }

    if (!path) {
      setResults([]);
      setLoading(false);
      return;
    }

    apiFetch({ path })
      .then((data) => {
        setResults(data);
        setLoading(false);
      })
      .catch(() => {
        setResults([]);
        setLoading(false);
      });
  }, [searchTerm, selection, postCategories]);

  return (
    <div>
      <SelectControl
        label={__('Type de contenu à lier', 'amnesty')}
        value={selection}
        options={masterOptions}
        onChange={(value) => {
          setSelection(value);
          setSearchTerm('');
          setResults([]);
          onPostSelect(null);
        }}
      />

      {selection && (
        <>
          <TextControl
            label={__('Rechercher un contenu spécifique', 'amnesty')}
            value={searchTerm}
            onChange={setSearchTerm}
            placeholder={__('Tapez au moins 2 caractères', 'amnesty')}
          />
          {loading && <Spinner />}
          {!loading && searchTerm.length >= 2 && (
            <ul
              style={{
                border: '1px solid #ccc',
                padding: 5,
                maxHeight: 200,
                overflowY: 'auto',
                margin: 0,
                listStyle: 'none',
              }}
            >
              {results.length > 0 ? (
                results.map((post) => {
                  const featuredImageUrl =
                    post._embedded?.['wp:featuredmedia']?.[0]?.source_url || '';
                  return (
                    <li
                      key={post.id}
                      style={{
                        cursor: 'pointer',
                        padding: '8px 10px',
                        display: 'flex',
                        alignItems: 'center',
                        gap: '0.75rem',
                        borderBottom: '1px solid #eee',
                      }}
                      onClick={() => {
                        onPostSelect(post);
                        setSearchTerm('');
                        setResults([]);
                      }}
                    >
                      {featuredImageUrl && (
                        <img
                          src={featuredImageUrl}
                          alt={post.title.rendered}
                          style={{ width: 40, height: 40, objectFit: 'cover', borderRadius: '2px' }}
                        />
                      )}
                      <strong dangerouslySetInnerHTML={{ __html: post.title.rendered }} />
                    </li>
                  );
                })
              ) : (
                <li style={{ padding: '8px 10px', fontStyle: 'italic', color: '#666' }}>
                  {__('Aucun résultat.', 'amnesty')}
                </li>
              )}
            </ul>
          )}
        </>
      )}
    </div>
  );
};

export default PostSearchControl;
