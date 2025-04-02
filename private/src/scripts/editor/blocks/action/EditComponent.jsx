import classnames from 'classnames';
import Campaign from './Campaign.jsx';
import Petition from './Petition.jsx';
import CustomButton from '../button/Button.jsx';

const { useEffect, useState } = wp.element;
const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, TextControl, SelectControl, Button } = wp.components;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const { type, title, subtitle, imageUrl, bgColor, buttonLink, buttonAlignment } = attributes;

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
        buttonLink: selectedPost.link,
      });
    }
  };

  return (
    <>
      <InspectorControls>
        <PanelBody initialOpen={true}>
          <SelectControl
            label={__('Type', 'amnesty')}
            value={type}
            options={[
              { label: __('Pétition', 'amnesty'), value: 'petition' },
              { label: __('Campagne', 'amnesty'), value: 'campaign' },
            ]}
            onChange={(value) => setAttributes({ type: value })}
          />
          <TextControl
            label={__('Titre', 'amnesty')}
            value={title}
            onChange={(value) => setAttributes({ title: value })}
          />
          <TextControl
            label={__('Sous titre', 'amnesty')}
            value={subtitle}
            onChange={(value) => setAttributes({ subtitle: value })}
          />
          <MediaUploadCheck>
            <MediaUpload
              onSelect={(media) => setAttributes({ imageUrl: media.url })}
              allowedTypes={['image']}
              render={({ open }) => (
                <Button onClick={open} isSecondary>
                  {__('Choisir une image', 'amnesty')}
                </Button>
              )}
            />
          </MediaUploadCheck>
          <SelectControl
            label={__('Couleur de fond', 'amnesty')}
            value={bgColor}
            options={[
              { label: 'Fond Blanc', value: 'bg-white' },
              { label: 'Fond Jaune', value: 'bg-yellow' },
            ]}
            onChange={(value) => setAttributes({ bgColor: value })}
          />
        </PanelBody>
        <PanelBody title={__('Paramètres du bouton', 'amnesty')} initialOpen={true}>
          {loading ? (
            <p>{__('Chargement des posts', 'amnesty')}</p>
          ) : (
            <SelectControl
              label={__('Sélectionner un post', 'amnesty')}
              value={buttonLink}
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
          <SelectControl
            label={__('Alignement', 'amnesty')}
            value={buttonAlignment}
            options={[
              { label: 'Gauche', value: 'left' },
              { label: 'Centre', value: 'center' },
              { label: 'Droite', value: 'right' },
            ]}
            onChange={(value) => setAttributes({ buttonAlignment: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className={classnames('action', type)}>
        <div className="header">
          <p className="title">{title}</p>
          <div className="container-image">
            <img className="action-image" src={imageUrl} />
          </div>
          <div className="subtitle-container">
            <p className="subtitle">{subtitle}</p>
          </div>
        </div>
        <div className={classnames('content', bgColor)}>
          {type === 'petition' && (
            <Petition
              progress={70}
              button={
                <CustomButton
                  label={'Signer la petition'}
                  size={'medium'}
                  icon={'pencil'}
                  link={buttonLink}
                  alignment={buttonAlignment}
                />
              }
            />
          )}
          {type === 'campaign' && (
            <Campaign
              button={
                <CustomButton
                  label={'Label campagne'}
                  size={'medium'}
                  icon={'arrow-right'}
                  link={buttonLink}
                  alignment={buttonAlignment}
                />
              }
            />
          )}
        </div>
      </div>
    </>
  );
};

export default EditComponent;
