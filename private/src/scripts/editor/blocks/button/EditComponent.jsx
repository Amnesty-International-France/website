import Button from './Button.jsx';

const { __ } = wp.i18n;
const { useEffect, useState } = wp.element;
const { useBlockProps, RichText, InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl } = wp.components;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const { label, size, style, icon, link, alignment } = attributes;

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
              value={link}
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
        <PanelBody title={__('Paramètres du bouton', 'amnesty')}>
          <SelectControl
            label={__('Taille', 'amnesty')}
            value={size}
            options={[
              { label: 'Petit', value: 'small' },
              { label: 'Moyen', value: 'medium' },
              { label: 'Grand', value: 'large' },
            ]}
            onChange={(value) => setAttributes({ size: value })}
          />
          <SelectControl
            label={__('Style', 'amnesty')}
            value={style}
            options={[
              { label: 'Contour Jaune', value: 'outline-yellow' },
              { label: 'Contour Noir', value: 'outline-black' },
              { label: 'Sans Contour', value: 'no-outline' },
              { label: 'Fond Noir', value: 'bg-black' },
              { label: 'Fond Jaune', value: 'bg-yellow' },
            ]}
            onChange={(value) => setAttributes({ style: value })}
          />
          <SelectControl
            label={__('Icône', 'amnesty')}
            value={icon}
            options={[
              { label: 'Aucune', value: '' },
              { label: 'Flèche gauche', value: 'arrow-left' },
              { label: 'Flèche droite', value: 'arrow-right' },
              { label: 'Loupe +', value: 'zoom-in' },
            ]}
            onChange={(value) => setAttributes({ icon: value })}
          />
          <SelectControl
            label={__('Alignement', 'amnesty')}
            value={alignment}
            options={[
              { label: 'Gauche', value: 'left' },
              { label: 'Centre', value: 'center' },
              { label: 'Droite', value: 'right' },
            ]}
            onChange={(value) => setAttributes({ alignment: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()}>
        <Button
          label={<RichText value={label} onChange={(value) => setAttributes({ label: value })} />}
          size={size}
          style={style}
          icon={icon}
          link={link}
          alignment={alignment}
        />
      </div>
    </>
  );
};

export default EditComponent;
