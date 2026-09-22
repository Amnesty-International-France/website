import PostSearchControl from '../../components/PostSearchControl.jsx';

const ServerSideRender = wp.serverSideRender;
const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, TextControl, SelectControl, ToggleControl, TextareaControl, Button } =
  wp.components;

const getCategoryLink = (slug) => {
  if (slug === 'landmark') {
    return '/reperes';
  }
  if (slug === 'page') {
    return '';
  }
  return `/${slug}`;
};

const EditComponent = ({ attributes, setAttributes }) => {
  const { custom, newTab, direction, title, surtitle, category, permalink, linkTitle, thumbnail, text } =
    attributes;

  const allowedTypesForThisBlock = ['post', 'pages', 'landmark', 'document'];

  const handleSelectImage = (newMedia) => {
    setAttributes({ thumbnail: newMedia.id });
  };

  const updateCustom = (value) => {
    setAttributes({ custom: value });
    if (value) {
      setAttributes({
        postId: null,
        title: '',
        surtitle: '',
        category: '',
        permalink: '',
        linkTitle: '',
        thumbnail: null,
        text: '',
      });
    } else {
      setAttributes({
        title: '',
        surtitle: '',
        category: '',
        permalink: '',
        linkTitle: '',
        thumbnail: null,
        text: '',
      });
    }
  };

  const updateNewTab = (value) => {
    setAttributes({ newTab: value });
  };

  const updateDirection = (value) => {
    setAttributes({ direction: value });
  };

  const updateTitle = (newTitle) => {
    setAttributes({ title: newTitle });
  };

  const updateSurtitle = (newSurtitle) => {
    setAttributes({ surtitle: newSurtitle });
  };

  const updateCategory = (newCategory) => {
    setAttributes({ category: newCategory });
  };

  const updatePermalink = (newPermalink) => {
    setAttributes({ permalink: newPermalink });
  };

  const updateLinkTitle = (newLinkTitle) => {
    setAttributes({ linkTitle: newLinkTitle});
  }

  const updateText = (newText) => {
    setAttributes({ text: newText });
  };

  const handlePostSelection = (post) => {
    if (!post) {
      setAttributes({
        postId: null,
        title: '',
        surtitle: '',
        permalink: '',
        linkTitle: '',
        thumbnail: null,
        text: '',
        category: '',
      });
      return;
    }

    if (post.type === 'document') {
      const { id, _embedded, excerpt, acf } = post;
      const docCategory = _embedded?.['wp:term']
        ?.flat()
        ?.find((term) => term.taxonomy === 'document_category')?.name;

      setAttributes({
        postId: id,
        category: docCategory || 'Document',
        title: post.title.rendered,
        surtitle: '',
        text: excerpt?.rendered || '',
        permalink: acf?.upload_du_document?.url || '',
        linkTitle: '',
        thumbnail: post.featured_media !== 0 ? post.featured_media : null,
      });
    } else {
      const { id, slug, _embedded, excerpt, type } = post;

      let postCategoryName = '';
      if (type === 'landmark') {
        postCategoryName = 'Repères';
      } else if (type === 'page') {
        postCategoryName = 'Page';
      } else {
        const postCategory = _embedded?.['wp:term']?.[0]?.find(
          (term) => term.taxonomy === 'category',
        );
        postCategoryName = postCategory?.name || '';
      }

      setAttributes({
        postId: id,
        title: post.title.rendered,
        surtitle: '',
        category: postCategoryName,
        permalink: `${getCategoryLink(type)}/${slug}`,
        linkTitle: '',
        thumbnail: _embedded?.['wp:featuredmedia']?.[0]?.id || null,
        text: excerpt?.rendered || '',
      });
    }
  };

  const linkProps = {
    href: permalink,
    className: 'card-image-text-block-link',
  };

  if (newTab) {
    linkProps.target = '_blank';
    linkProps.rel = 'noopener noreferrer';
  }

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du bloc', 'amnesty')} initialOpen={true}>
          <ToggleControl
            __nextHasNoMarginBottom
            label={__('Contenu personnalisé', 'amnesty')}
            checked={custom}
            onChange={updateCustom}
          />
          <ToggleControl
            __nextHasNoMarginBottom
            label={__('Ouvrir dans un nouvel onglet', 'amnesty')}
            checked={newTab}
            onChange={updateNewTab}
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
          {!custom && (
            <>
              <PostSearchControl
                allowedTypes={allowedTypesForThisBlock}
                onPostSelect={handlePostSelection}
              />
              {title && (
                <p style={{ fontStyle: 'italic', marginTop: '1rem' }}>
                  {__('Contenu sélectionné :', 'amnesty')} <strong>{title}</strong>
                </p>
              )}
            </>
          )}
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
                label={__('Catégorie', 'amnesty')}
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
                label={__('Surtitre', 'amnesty')}
                value={surtitle}
                onChange={updateSurtitle}
                placeholder={__('Entrez un surtitre…', 'amnesty')}
              />
              <TextControl
                label={__('Lien', 'amnesty')}
                value={permalink}
                onChange={updatePermalink}
                placeholder={__('Lien', 'amnesty')}
              />
              <TextControl
                label={__('Intitulé du lien', 'amnesty')}
                value={linkTitle}
                onChange={updateLinkTitle}
                placeholder={__('Voir la suite', 'amnesty')}
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

      <div {...useBlockProps()}>
        <ServerSideRender
          block="amnesty-core/card-image-text"
          attributes={{ ...attributes, editor: true }}
          EmptyResponsePlaceholder={() => <p>Chargement de l&apos;aperçu...</p>}
        />
      </div>
    </>
  );
};

export default EditComponent;
