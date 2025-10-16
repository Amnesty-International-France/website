import Button from './Button.jsx';
import PostSearchControl from '../../components/PostSearchControl.jsx';

const { __ } = wp.i18n;
const { useEffect } = wp.element;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl, ToggleControl, Button: WpButton } = wp.components;
const apiFetch = wp.apiFetch;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const {
    label,
    size,
    style,
    icon,
    linkType,
    externalUrl,
    alignment,
    internalUrl,
    internalUrlTitle,
    postId,
    targetBlank,
  } = attributes;

  const allowedTypesForThisBlock = [
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

  useEffect(() => {
    if (postId && !internalUrl) {
      apiFetch({ path: `/wp/v2/posts/${postId}?_fields=link,title,type` })
        .catch(() => apiFetch({ path: `/wp/v2/pages/${postId}?_fields=link,title,type` }))
        .then((post) => {
          if (post) {
            setAttributes({
              internalUrl: post.link,
              internalUrlTitle: post.title.rendered,
              postType: post.type,
            });
          }
        })
        .catch(() => {
          console.warn(`Impossible de trouver le contenu pour le postId: ${postId}`);
        });
    }
  }, [postId, internalUrl]);

  const handlePostSelect = (post) => {
    if (post) {
      setAttributes({
        internalUrl: post.link,
        internalUrlTitle: post.title.rendered,
        postId: post.id,
        postType: post.type,
      });
    } else {
      setAttributes({
        internalUrl: '',
        internalUrlTitle: '',
        postId: 0,
        postType: '',
      });
    }
  };

  const handleRemoveLink = () => {
    setAttributes({ internalUrl: '', internalUrlTitle: '', postId: 0, postType: '' });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du lien', 'amnesty')} initialOpen={true}>
          <SelectControl
            label={__('Type de lien', 'amnesty')}
            value={linkType}
            options={[
              { label: 'Lien interne (contenu du site)', value: 'internal' },
              { label: 'Lien externe (URL)', value: 'external' },
            ]}
            onChange={(value) => setAttributes({ linkType: value })}
          />

          {linkType === 'internal' && (
            <>
              {!internalUrl ? (
                <PostSearchControl
                  onPostSelect={handlePostSelect}
                  allowedTypes={allowedTypesForThisBlock}
                />
              ) : (
                <div style={{ marginTop: '10px', paddingTop: '10px', borderTop: '1px solid #ccc' }}>
                  <p style={{ margin: 0 }}>
                    {__('Lien sélectionné :', 'amnesty')}{' '}
                    <strong dangerouslySetInnerHTML={{ __html: internalUrlTitle }} />
                  </p>
                  <WpButton isLink isDestructive onClick={handleRemoveLink}>
                    {__('Retirer le lien', 'amnesty')}
                  </WpButton>
                </div>
              )}
            </>
          )}

          {linkType === 'external' && (
            <TextControl
              label={__('URL du lien externe', 'amnesty')}
              value={externalUrl}
              placeholder="https://exemple.com"
              onChange={(value) => setAttributes({ externalUrl: value })}
            />
          )}

          {(internalUrl || externalUrl) && (
            <ToggleControl
              label={__('Ouvrir dans un nouvel onglet', 'amnesty')}
              checked={!!targetBlank}
              onChange={(value) => setAttributes({ targetBlank: value })}
              help={
                targetBlank
                  ? __("Le lien s'ouvrira dans un nouvel onglet.", 'amnesty')
                  : __("Le lien s'ouvrira dans le même onglet.", 'amnesty')
              }
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
              { label: 'Coeur', value: 'heart' },
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
          link={linkType === 'external' ? externalUrl : internalUrl || '#'}
          alignment={alignment}
        />
      </div>
    </>
  );
};

export default EditComponent;
