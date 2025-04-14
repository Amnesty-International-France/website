const { __ } = wp.i18n;
const { useEffect, useState } = wp.element;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl } = wp.components;

const EditComponent = ({ attributes, setAttributes }) => {
  const { postId } = attributes;

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
    const selectedPostId = parseInt(selectedId, 10);
    setAttributes({ postId: selectedPostId });
  };

  const selectedPost = posts.find((post) => post.id === postId);

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du lien', 'amnesty')} initialOpen={true}>
          {loading ? (
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
          )}
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps({ className: 'read-also-block' })}>
        {postId && selectedPost ? (
          <p>
            {__('À lire aussi', 'amnesty')} :{' '}
            <a href={selectedPost.link} target="_blank" rel="noopener noreferrer">
              {selectedPost.title.rendered}
            </a>
          </p>
        ) : (
          <p>{__('Sélectionnez un post', 'amnesty')}</p>
        )}
      </div>
    </>
  );
};

export default EditComponent;
