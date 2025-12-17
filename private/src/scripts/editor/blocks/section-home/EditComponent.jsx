import classnames from 'classnames';
import Icon from '../../components/Icon.jsx';
import CustomButton from '../button/Button.jsx';
import IconPickerControl from '../../../admin/components/IconPickerControl.jsx';

const { useEffect, useState } = wp.element;
const { useBlockProps, InspectorControls, MediaUpload } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl, Button, TextareaControl, ToggleControl, Spinner } =
  wp.components;
const { __ } = wp.i18n;
const apiFetch = wp.apiFetch;
const { useSelect } = wp.data;

const PageSearchControl = ({ selectedPageUrl, selectedPageTitle, onChange }) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!searchTerm) {
      setResults([]);
      return;
    }

    setLoading(true);
    const path = `/wp/v2/pages?search=${encodeURIComponent(searchTerm)}&per_page=10`;

    apiFetch({ path })
      .then((pages) => {
        setResults(pages);
        setLoading(false);
      })
      .catch(() => {
        setResults([]);
        setLoading(false);
      });
  }, [searchTerm]);

  return (
    <div>
      <TextControl
        label={__('Rechercher une page', 'amnesty')}
        value={searchTerm}
        onChange={setSearchTerm}
        placeholder={__('Tapez pour chercher', 'amnesty')}
      />
      {loading && <Spinner />}
      {!loading && results.length > 0 && (
        <ul
          style={{
            border: '1px solid #ccc',
            padding: 5,
            maxHeight: 150,
            overflowY: 'auto',
            margin: 0,
            listStyle: 'none',
          }}
        >
          {results.map((page) => (
            <li
              key={page.id}
              style={{
                cursor: 'pointer',
                padding: '8px 10px',
                borderBottom: '1px solid #eee',
              }}
              onClick={() => {
                onChange(page.link, page.title.rendered);
                setSearchTerm('');
                setResults([]);
              }}
              dangerouslySetInnerHTML={{ __html: page.title.rendered }}
            />
          ))}
        </ul>
      )}
      {selectedPageUrl && (
        <div style={{ marginTop: '10px' }}>
          <p style={{ margin: 0 }}>
            {__('Page sélectionnée :', 'amnesty')}{' '}
            <strong dangerouslySetInnerHTML={{ __html: selectedPageTitle }} />
          </p>
          <Button isLink isDestructive onClick={() => onChange('', '')}>
            {__('Retirer', 'amnesty')}
          </Button>
        </div>
      )}
    </div>
  );
};

const ContentSearchControl = ({ contentType, onChange }) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);

  const categories = useSelect(
    (select) => select('core').getEntityRecords('taxonomy', 'category', { per_page: 100 }),
    [],
  );

  useEffect(() => {
    if (!searchTerm || searchTerm.length < 2 || !contentType) {
      setResults([]);
      return;
    }
    setLoading(true);
    let path = '';

    const fetchContent = (apiPath) => {
      apiFetch({ path: apiPath })
        .then((items) => {
          setResults(items);
          setLoading(false);
        })
        .catch(() => {
          setResults([]);
          setLoading(false);
        });
    };

    switch (contentType) {
      case 'page':
        path = `/wp/v2/pages?search=${encodeURIComponent(searchTerm)}&per_page=10`;
        break;
      case 'landmark':
      case 'training':
      case 'edh':
      case 'petition':
      case 'document':
        path = `/wp/v2/${contentType}?search=${encodeURIComponent(searchTerm)}&per_page=10`;
        break;
      default: {
        const categoryObj = categories?.find((cat) => cat.slug === contentType);
        if (categoryObj) {
          path = `/wp/v2/posts?search=${encodeURIComponent(
            searchTerm,
          )}&category=${categoryObj.id}&per_page=10`;
        }
        break;
      }
    }

    if (path) {
      fetchContent(path);
    } else {
      setResults([]);
      setLoading(false);
    }
  }, [searchTerm, contentType, categories]);

  return (
    <div style={{ marginTop: '10px' }}>
      <TextControl
        label={__('Rechercher un contenu', 'amnesty')}
        value={searchTerm}
        onChange={setSearchTerm}
        placeholder={__('Tapez au moins 2 caractères', 'amnesty')}
      />
      {loading && <Spinner />}
      {!loading && results.length > 0 && (
        <ul
          style={{
            border: '1px solid #ccc',
            padding: 5,
            maxHeight: 150,
            overflowY: 'auto',
            margin: 0,
            listStyle: 'none',
          }}
        >
          {results.map((item) => (
            <li
              key={item.id}
              style={{
                cursor: 'pointer',
                padding: '8px 10px',
                borderBottom: '1px solid #eee',
              }}
              onClick={() => {
                onChange(item.link, item.title.rendered);
                setSearchTerm('');
                setResults([]);
              }}
            >
              <strong dangerouslySetInnerHTML={{ __html: item.title.rendered }} />
            </li>
          ))}
        </ul>
      )}
    </div>
  );
};

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const {
    title,
    text,
    bgColor,
    showImage,
    mediaId,
    mediaUrl,
    mediaPosition,
    mediaCaption,
    mediaDescription,
    icons,
    displayButton,
    buttonLabel,
    buttonLink,
    buttonPosition,
    buttonContentType,
    buttonLinkTitle,
    isExternal,
  } = attributes;

  const contentTypes = useSelect((select) => {
    const postCategories = select('core').getEntityRecords('taxonomy', 'category', {
      per_page: 100,
    });
    const hardcodedCpts = [
      { name: 'Pages', slug: 'page' },
      { name: 'Repères', slug: 'landmark' },
      { name: 'Formations', slug: 'training' },
      { name: 'Ressources pédagogiques', slug: 'edh' },
      { name: 'Pétitions', slug: 'petition' },
      { name: 'Documents', slug: 'document' },
    ];

    if (!postCategories) {
      return [{ label: __('Chargement', 'amnesty'), value: '' }];
    }

    const categoryOptions = postCategories
      .filter((cat) => cat.name !== 'Non classé')
      .map((cat) => ({ label: cat.name, value: cat.slug }));

    const cptOptions = hardcodedCpts.map((cpt) => ({ label: cpt.name, value: cpt.slug }));

    return [
      { label: __('Sélectionnez un type de contenu', 'amnesty'), value: '' },
      ...cptOptions,
      ...categoryOptions,
    ];
  }, []);

  const onSelectImage = (media) => {
    setAttributes({
      mediaId: media.id,
      mediaUrl: media.url,
      mediaCaption: media.caption,
      mediaDescription: media.description,
    });
  };

  const addIcon = () => {
    setAttributes({ icons: [...icons, { icon: '', text: '', link: '', linkTitle: '' }] });
  };
  const updateIconLink = (index, link, newTitle) => {
    const newIcons = [...icons];
    newIcons[index].link = link;
    newIcons[index].linkTitle = newTitle;
    setAttributes({ icons: newIcons });
  };
  const updateIconField = (index, key, value) => {
    const newIcons = [...icons];
    newIcons[index][key] = value;
    setAttributes({ icons: newIcons });
  };
  const removeIcon = (index) => {
    const newIcons = icons.filter((_, i) => i !== index);
    setAttributes({ icons: newIcons });
  };

  const renderIconBlock = (iconItem) => {
    if (!iconItem.icon) return null;
    return (
      <div className="icon-with-text">
        <div className="container-icon">
          <Icon name={iconItem.icon} colorClass={bgColor === 'black' ? 'primary' : 'black'} />
        </div>
        {iconItem.text && (
          <p className={classnames('icon-description', bgColor)}>{iconItem.text}</p>
        )}
      </div>
    );
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Contenu', 'amnesty')}>
          <TextControl
            label={__('Titre', 'amnesty')}
            value={title}
            onChange={(value) => setAttributes({ title: value })}
          />
          <TextareaControl
            label={__('Texte', 'amnesty')}
            value={text}
            onChange={(value) => setAttributes({ text: value })}
          />
          <SelectControl
            label={__('Couleur de fond', 'amnesty')}
            value={bgColor}
            options={[
              { label: __('Blanc', 'amnesty'), value: 'white' },
              { label: __('Gris clair', 'amnesty'), value: 'grey-lighter' },
              { label: __('Gris', 'amnesty'), value: 'grey' },
              { label: __('Noir', 'amnesty'), value: 'black' },
            ]}
            onChange={(value) => setAttributes({ bgColor: value })}
          />
          <ToggleControl
            label={__('Afficher l’image', 'amnesty')}
            checked={showImage}
            onChange={(value) => setAttributes({ showImage: value })}
          />
          {showImage && (
            <>
              <MediaUpload
                onSelect={onSelectImage}
                allowedTypes={['image']}
                value={mediaId}
                render={({ open }) => (
                  <Button onClick={open} isSecondary>
                    {mediaId
                      ? __('Changer la photo', 'amnesty')
                      : __('Ajouter une photo', 'amnesty')}{' '}
                  </Button>
                )}
              />
              <SelectControl
                label={__('Position de la photo', 'amnesty')}
                value={mediaPosition}
                options={[
                  { label: __('Gauche', 'amnesty'), value: 'left' },
                  { label: __('Droite', 'amnesty'), value: 'right' },
                ]}
                onChange={(value) => setAttributes({ mediaPosition: value })}
              />
            </>
          )}
        </PanelBody>
        <PanelBody title={__('Icônes', 'amnesty')}>
          {icons.map((iconItem, index) => (
            <div
              key={index}
              style={{ border: '1px solid #eee', padding: '10px', marginBottom: '10px' }}
            >
              <IconPickerControl
                label={`Icône ${index + 1}`}
                value={iconItem.icon}
                onChange={(value) => updateIconField(index, 'icon', value)}
              />
              {iconItem.icon && (
                <>
                  <TextControl
                    label={`Texte sous l'icône ${index + 1}`}
                    value={iconItem.text}
                    onChange={(value) => updateIconField(index, 'text', value)}
                  />
                  <PageSearchControl
                    selectedPageUrl={iconItem.link}
                    selectedPageTitle={iconItem.linkTitle}
                    onChange={(link, newTitle) => updateIconLink(index, link, newTitle)}
                  />
                </>
              )}
              <Button isDestructive onClick={() => removeIcon(index)}>
                {__('Supprimer cette icône', 'amnesty')}{' '}
              </Button>
            </div>
          ))}
          {icons.length < 4 && (
            <Button isPrimary onClick={addIcon}>
              {__('Ajouter une icône', 'amnesty')}{' '}
            </Button>
          )}
        </PanelBody>
        <PanelBody title={__('Bouton', 'amnesty')}>
          <ToggleControl
            label={__('Afficher bouton', 'amnesty')}
            checked={displayButton}
            onChange={(value) => setAttributes({ displayButton: value })}
          />
          {displayButton && (
            <>
              <TextControl
                label={__('Label du bouton', 'amnesty')}
                value={buttonLabel}
                onChange={(value) => setAttributes({ buttonLabel: value })}
              />
              <ToggleControl
                label={__('Lien externe', 'amnesty')}
                checked={isExternal}
                onChange={(value) => setAttributes({ isExternal: value })}
              />
              {isExternal ? (
                <TextControl
                  label={__('Lien externe (URL)', 'amnesty')}
                  value={buttonLink}
                  onChange={(value) => setAttributes({ buttonLink: value })}
                />
              ) : (
                <>
                  <SelectControl
                    label={__('Type de contenu pour le lien', 'amnesty')}
                    value={buttonContentType}
                    options={contentTypes}
                    onChange={(value) => {
                      setAttributes({
                        buttonContentType: value,
                        buttonLink: '',
                        buttonLinkTitle: '',
                      });
                    }}
                  />
                  {buttonContentType && (
                    <ContentSearchControl
                      contentType={buttonContentType}
                      onChange={(link, newTitle) => {
                        setAttributes({ buttonLink: link, buttonLinkTitle: newTitle });
                      }}
                    />
                  )}
                  {buttonLink && buttonLinkTitle && (
                    <div
                      style={{ marginTop: '10px', borderTop: '1px solid #eee', paddingTop: '10px' }}
                    >
                      <p style={{ margin: 0 }}>
                        {__('Lien sélectionné :', 'amnesty')}{' '}
                        <strong dangerouslySetInnerHTML={{ __html: buttonLinkTitle }} />
                      </p>
                      <Button
                        isLink
                        isDestructive
                        onClick={() => setAttributes({ buttonLink: '', buttonLinkTitle: '' })}
                      >
                        {__('Retirer', 'amnesty')}
                      </Button>
                    </div>
                  )}
                </>
              )}
              <SelectControl
                label={__('Position du bouton', 'amnesty')}
                value={buttonPosition}
                options={[
                  { label: __('Gauche', 'amnesty'), value: 'left' },
                  { label: __('Droite', 'amnesty'), value: 'right' },
                  { label: __('Centre', 'amnesty'), value: 'center' },
                ]}
                onChange={(value) => setAttributes({ buttonPosition: value })}
              />
            </>
          )}
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className={classnames('section-home', bgColor)}>
        <div
          className={classnames('section-inner', {
            [mediaPosition]: showImage && mediaUrl,
            'with-image': showImage && mediaUrl,
            'without-image': !showImage || !mediaUrl,
          })}
        >
          {showImage && mediaUrl && (
            <div className="section-media">
              <div className="section-media-image-wrapper">
                <img
                  className="section-media-image"
                  src={mediaUrl}
                  alt={__('Image de la section', 'amnesty')}
                />
                {(mediaCaption || mediaDescription) && (
                  <p className="section-media-caption-description">
                    {`${mediaCaption} /`} {mediaDescription}{' '}
                  </p>
                )}
              </div>
            </div>
          )}
          <div className="section-content">
            <h2 className="title">{title}</h2>
            {text && <p className="text">{text}</p>}
            <div className="icons">
              {icons.map((iconItem, index) => {
                const iconBlock = renderIconBlock(iconItem);
                if (!iconBlock) return null;
                if (iconItem.link)
                  return (
                    <a key={index} href={iconItem.link}>
                      {iconBlock}
                    </a>
                  );
                return <div key={index}>{iconBlock}</div>;
              })}
            </div>
            {displayButton && (
              <CustomButton
                label={buttonLabel}
                size="medium"
                link={buttonLink}
                alignment={buttonPosition}
                style="bg-yellow"
                icon="arrow-right"
                isInternal={!isExternal}
              />
            )}
          </div>
        </div>
      </div>
    </>
  );
};

export default EditComponent;
