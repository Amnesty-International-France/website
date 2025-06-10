import classnames from 'classnames';
import CustomButton from '../button/Button.jsx';
import Icon from '../../components/Icon.jsx';

const { useEffect, useState } = wp.element;
const { __ } = wp.i18n;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl } = wp.components;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const { title, titleSize, description, icon, bgColor, buttonLink } = attributes;

  const reqSvgs = require.context('../../icons', false, /\.svg$/);

  const iconOptions = reqSvgs.keys().map((filePath) => {
    const fileName = filePath.replace('./', '').replace('.svg', '');
    return {
      label: fileName,
      value: fileName,
    };
  });

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

  const handlePostSelect = (selectedLink) => {
    setAttributes({
      buttonLink: selectedLink,
    });
  };

  const selectedPost = posts.find((post) => post.link === buttonLink);

  const postOptions = [
    { label: __('Choisir un post', 'amnesty'), value: '' },
    ...(selectedPost
      ? [
          {
            label: selectedPost.title.rendered,
            value: selectedPost.link,
          },
        ]
      : []),
    ...posts
      .filter((post) => post.link !== buttonLink)
      .map((post) => ({
        label: post.title.rendered,
        value: post.link,
      })),
  ];

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
          {loading ? (
            <p>{__('Chargement des posts', 'amnesty')}</p>
          ) : (
            <SelectControl
              label={__('Lien du bouton', 'amnesty')}
              value={buttonLink || ''}
              options={postOptions}
              onChange={handlePostSelect}
            />
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
