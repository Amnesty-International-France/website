const { __ } = wp.i18n;
const { useEffect, useState } = wp.element;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl } = wp.components;

const EditComponent = ({ attributes, setAttributes }) => {
  const { postId, linkType = 'internal', externalUrl = '', externalLabel = '' } = attributes;

  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/wp-json/wp/v2/posts?per_page=100')
      .then((response) => response.json())
      .then((data) => {
        setPosts(data);
        setLoading(false);
      })
      .catch((error) => {
        console.error('Erreur de récupération des posts', error);
        setLoading(false);
      });
  }, []);

  const handlePostSelect = (selectedId) => {
    if (!selectedId) {
      setAttributes({ postId: null });
      return;
    }

    const selectedPostId = parseInt(selectedId, 10);
    setAttributes({ postId: selectedPostId });
  };

  const selectedPost = posts.find((post) => post.id === postId);

  let linkContent;

  if (linkType === 'internal' && postId && selectedPost) {
    linkContent = (
      <a href={selectedPost.link} target="_blank" rel="noopener noreferrer">
        {selectedPost.title.rendered}
      </a>
    );
  } else if (linkType === 'external' && externalUrl) {
    linkContent = (
      <a href={externalUrl} target="_blank" rel="noopener noreferrer">
        {externalLabel || externalUrl}
      </a>
    );
  } else {
    linkContent = (
      <span>
        {linkType === 'external'
          ? __('Aucun lien externe fourni.', 'amnesty')
          : __('Aucun article sélectionné.', 'amnesty')}
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
              { label: __('Interne', 'amnesty'), value: 'internal' },
              { label: __('Externe', 'amnesty'), value: 'external' },
            ]}
            onChange={(value) => setAttributes({ linkType: value })}
          />

          {linkType === 'internal' &&
            (loading ? (
              <p>{__('Chargement des posts…', 'amnesty')}</p>
            ) : (
              <SelectControl
                label={__('Sélectionner un post', 'amnesty')}
                value={postId ? postId.toString() : ''}
                options={[
                  { label: __('Choisir un post', 'amnesty'), value: '' },
                  ...posts.map((post) => ({
                    label: post.title.rendered,
                    value: post.id.toString(),
                  })),
                ]}
                onChange={handlePostSelect}
              />
            ))}

          {linkType === 'external' && (
            <>
              <TextControl
                label={__('URL externe', 'amnesty')}
                value={externalUrl}
                onChange={(value) => setAttributes({ externalUrl: value })}
              />
              <TextControl
                label={__('Label du lien', 'amnesty')}
                value={externalLabel}
                onChange={(value) => setAttributes({ externalLabel: value })}
              />
            </>
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
