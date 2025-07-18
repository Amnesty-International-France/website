import classnames from 'classnames';

const { __ } = wp.i18n;
const { useEffect, useState } = wp.element;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, TextControl, SelectControl, ToggleControl, TextareaControl, Button } =
  wp.components;
const { useSelect } = wp.data;

const EditComponent = ({ attributes, setAttributes }) => {
  const { custom, direction, postId, title, subtitle, category, permalink, thumbnail, text } =
    attributes;

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

  const selectedMedia = useSelect(
    (select) => (thumbnail ? select('core').getMedia(thumbnail) : null),
    [thumbnail],
  );

  const handleSelectImage = (newMedia) => {
    setAttributes({ thumbnail: newMedia.id });
  };

  const updateCustom = (value) => {
    setAttributes({ custom: value });
  };

  const updateDirection = (value) => {
    setAttributes({ direction: value });
  };

  const updateTitle = (newTitle) => {
    setAttributes({ title: newTitle });
  };

  const updateSubtitle = (newSubtitle) => {
    setAttributes({ subtitle: newSubtitle });
  };

  const updateCategory = (newCategory) => {
    setAttributes({ category: newCategory });
  };

  const updatePermalink = (newPermalink) => {
    setAttributes({ permalink: newPermalink });
  };

  const updateText = (newText) => {
    setAttributes({ text: newText });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du bloc', 'amnesty')} initialOpen={true}>
          <ToggleControl
            __nextHasNoMarginBottom
            label={__('Custom', 'amnesty')}
            checked={custom}
            onChange={updateCustom}
          />
          <SelectControl
            label={__('Disposition', 'amnesty')}
            value={direction}
            options={[
              { label: __('Horizontal', 'amnesty'), value: 'horizontal' },
              { label: __('Vertical', 'amnesty'), value: 'vertical' },
            ]}
            onChange={updateDirection}
          />
          {!custom &&
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
          {custom && (
            <>
              <MediaUploadCheck>
                <MediaUpload
                  onSelect={handleSelectImage}
                  allowedTypes={['image']}
                  value={thumbnail}
                  render={({ open }) => (
                    <Button onClick={open} isPrimary>
                      {thumbnail
                        ? __('Changer l’image', 'amnesty')
                        : __('Ajouter une image', 'amnesty')}
                    </Button>
                  )}
                />
              </MediaUploadCheck>
              <TextControl
                label={__('Category', 'amnesty')}
                value={category}
                onChange={updateCategory}
                placeholder={__('Entrez une catégorie…', 'amnesty')}
              />
              <TextControl
                label={__('Titre', 'amnesty')}
                value={title}
                onChange={updateTitle}
                placeholder={__('Entrez un titre…', 'amnesty')}
              />
              <TextControl
                label={__('Sous-titre', 'amnesty')}
                value={subtitle}
                onChange={updateSubtitle}
                placeholder={__('Entrez un sous-titre…', 'amnesty')}
              />
              <TextControl
                label={__('Lien', 'amnesty')}
                value={permalink}
                onChange={updatePermalink}
                placeholder={__('Lien', 'amnesty')}
              />
              <TextareaControl
                __nextHasNoMarginBottom
                label={__('Texte', 'amnesty')}
                value={text}
                onChange={updateText}
                placeholder={__('Texte', 'amnesty')}
              />
            </>
          )}
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className={classnames('card-image-text-block', direction)}>
        {selectedMedia}
      </div>
    </>
  );
};

export default EditComponent;
