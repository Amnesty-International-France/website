const { __ } = wp.i18n;
const { useEffect, useState } = wp.element;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl } = wp.components;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;

  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/wp-json/wp/v2/posts')
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

  const handlePostSelect = (selectedPostId) => {
    const selectedPost = posts.find((post) => post.id === parseInt(selectedPostId, 10));
    if (selectedPost) {
      setAttributes({
        link: selectedPost.link,
        text: selectedPost.title.rendered,
      });
    }
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du lien', 'amnesty')} initialOpen={true}>
          {loading ? (
            <p>{__('Chargement des posts', 'amnesty')}</p>
          ) : (
            <SelectControl
              label={__('Sélectionner un post', 'amnesty')}
              value={attributes.link}
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
        <p>
          {__('À lire aussi', 'amnesty')}:
          <a href={attributes.link} target="_blank" rel="noopener noreferrer">
            {attributes.text || __('Sélectionnez un post', 'amnesty')}
          </a>
        </p>
      </div>
    </>
  );
};

export default EditComponent;
