import Button from './Button.jsx';

const { __ } = wp.i18n;
const { useEffect, useState } = wp.element;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl } = wp.components;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const { postId, label, size, style, icon, linkType, externalUrl, alignment } = attributes;

  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [internalLink, setInternalLink] = useState('');

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
        postId: selectedPost.id,
      });
      setInternalLink(selectedPost.link);
    }
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du lien', 'amnesty')} initialOpen={true}>
          <SelectControl
            label={__('Type de lien', 'amnesty')}
            value={linkType}
            options={[
              { label: 'Lien interne (post)', value: 'internal' },
              { label: 'Lien externe (URL)', value: 'external' },
            ]}
            onChange={(value) => setAttributes({ linkType: value, link: '' })}
          />

          {linkType === 'internal' &&
            (loading ? (
              <p>{__('Chargement des posts', 'amnesty')}</p>
            ) : (
              <SelectControl
                label={__('Sélectionner un post', 'amnesty')}
                value={postId?.toString() || ''}
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
            <TextControl
              label={__('URL du lien externe', 'amnesty')}
              value={externalUrl}
              placeholder="https://exemple.com"
              onChange={(value) => setAttributes({ externalUrl: value })}
            />
          )}
        </PanelBody>
        <PanelBody title={__('Paramètres du bouton', 'amnesty')}>
          <TextControl
            label={__('Texte du bouton', 'amnesty')}
            value={label}
            placeholder={__('Ex: En savoir plus', 'amnesty')}
            onChange={(value) => setAttributes({ label: value })}
          />
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
          label={label}
          size={size}
          style={style}
          icon={icon}
          link={linkType === 'external' ? externalUrl : internalLink}
          alignment={alignment}
        />
      </div>
    </>
  );
};

export default EditComponent;
