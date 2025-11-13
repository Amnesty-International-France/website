import classnames from 'classnames';
import PostSearchControl from '../../components/PostSearchControl.jsx';

const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, TextControl, SelectControl, ToggleControl, TextareaControl, Button } =
  wp.components;
const { useSelect } = wp.data;

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
  const { custom, newTab, direction, title, subtitle, category, permalink, thumbnail, text } =
    attributes;

  const blockProps = useBlockProps();

  const allowedTypesForThisBlock = ['post', 'pages', 'landmark', 'document'];

  const selectedMedia = useSelect(
    (select) => (thumbnail ? select('core').getMedia(thumbnail) : null),
    [thumbnail],
  );

  const handleSelectImage = (newMedia) => {
    setAttributes({ thumbnail: newMedia.id });
  };

  const updateCustom = (value) => {
    setAttributes({ custom: value });
    if (value) {
      setAttributes({
        postId: null,
        title: '',
        subtitle: '',
        category: '',
        permalink: '',
        thumbnail: null,
        text: '',
        selectedPostDate: '',
        selectedPostCustomTerms: [],
      });
    } else {
      setAttributes({
        title: '',
        subtitle: '',
        category: '',
        permalink: '',
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

  const extractAllCustomTerms = (embeddedData) => {
    if (!embeddedData || !Array.isArray(embeddedData['wp:term'])) {
      return [];
    }
    let allCustomTerms = [];
    embeddedData['wp:term'].forEach((termGroup) => {
      if (Array.isArray(termGroup)) {
        const customTermsInGroup = termGroup.filter(
          (term) => term.taxonomy !== 'category' && term.taxonomy !== 'post_tag',
        );
        allCustomTerms = allCustomTerms.concat(
          customTermsInGroup.map(({ id, name, slug, taxonomy }) => ({ id, name, slug, taxonomy })),
        );
      }
    });
    return allCustomTerms;
  };

  const handlePostSelection = (post) => {
    if (!post) {
      setAttributes({
        postId: null,
        title: '',
        subtitle: '',
        permalink: '',
        thumbnail: null,
        text: '',
        category: '',
      });
      return;
    }

    if (post.type === 'document') {
      const { id, _embedded, excerpt, date, acf } = post;
      const docCategory = _embedded?.['wp:term']
        ?.flat()
        ?.find((term) => term.taxonomy === 'document_category')?.name;

      setAttributes({
        postId: id,
        category: docCategory || 'Document',
        title: post.title.rendered,
        subtitle: '',
        text: excerpt?.rendered || '',
        selectedPostDate: date,
        selectedPostCustomTerms: [],
        permalink: acf?.upload_du_document?.url || '',
        thumbnail: post.featured_media !== 0 ? post.featured_media : null,
      });
    } else {
      const { id, slug, _embedded, date, excerpt, type } = post;
      const allExtractedTerms = extractAllCustomTerms(_embedded);

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
        subtitle: '',
        category: postCategoryName,
        permalink: `${getCategoryLink(type)}/${slug}`,
        thumbnail: _embedded?.['wp:featuredmedia']?.[0]?.id || null,
        text: excerpt?.rendered || '',
        selectedPostDate: date,
        selectedPostCustomTerms: allExtractedTerms,
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

      <div {...blockProps} className={classnames('card-image-text-block', direction)}>
        <p className="card-image-text-category">{category}</p>
        <div className="card-content-wrapper">
          <div {...linkProps}>
            <div className="card-image-text-thumbnail-wrapper">
              {selectedMedia && (
                <img className="card-image-text-thumbnail" src={selectedMedia.source_url} alt="" />
              )}
            </div>
            <div className="card-image-text-content-container">
              <div className="card-image-text-content">
                <p className="card-image-text-content-subtitle">{subtitle}</p>
                <p className="card-image-text-content-title">{title}</p>
                <p
                  className="card-image-text-content-text"
                  dangerouslySetInnerHTML={{ __html: text }}
                />
                <div className="card-image-text-content-see-more">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    viewBox="0 0 16 16"
                    fill="none"
                  >
                    <path
                      fillRule="evenodd"
                      clipRule="evenodd"
                      d="M10.7826 7.33336L7.20663 3.75736L8.14930 2.81470L13.3346 8.00003L8.14930 13.1854L7.20663 12.2427L10.7826 8.66670H2.66797V7.33336H10.7826Z"
                      fill="black"
                    />
                  </svg>
                  <p className="card-image-text-content-see-more-label">Voir la suite</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

export default EditComponent;
