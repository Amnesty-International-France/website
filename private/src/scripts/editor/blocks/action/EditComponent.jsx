import classnames from 'classnames';
import CustomButton from '../button/Button.jsx';

const { useEffect, useState } = wp.element;
const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, TextControl, SelectControl, Button, Spinner, TextareaControl } = wp.components;
const { useSelect } = wp.data;
const apiFetch = wp.apiFetch;

const formatDate = (yyyymmdd) => {
  if (!yyyymmdd || typeof yyyymmdd !== 'string' || yyyymmdd.length !== 8) {
    return yyyymmdd;
  }
  const year = yyyymmdd.substring(0, 4);
  const month = yyyymmdd.substring(4, 6);
  const day = yyyymmdd.substring(6, 8);
  return `${day}.${month}.${year}`;
};

const PetitionSearchControl = ({ onPetitionSelect }) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!searchTerm) {
      setResults([]);
      return;
    }
    setLoading(true);
    const path = `/wp/v2/petition?search=${encodeURIComponent(searchTerm)}&per_page=10&_embed`;
    apiFetch({ path })
      .then((petitions) => {
        setResults(petitions);
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
        label={__('Rechercher une pétition active', 'amnesty')}
        value={searchTerm}
        onChange={setSearchTerm}
        placeholder={__('Tapez pour rechercher', 'amnesty')}
      />
      {loading && <Spinner />}
      {!loading && searchTerm.length > 0 && (
        <ul
          style={{
            border: '1px solid #ccc',
            padding: 5,
            maxHeight: 200,
            overflowY: 'auto',
            margin: 0,
            listStyle: 'none',
          }}
        >
          {results.length > 0 ? (
            results.map((petition) => (
              <li
                key={petition.id}
                style={{
                  cursor: 'pointer',
                  padding: '8px 10px',
                  borderBottom: '1px solid #eee',
                }}
                onClick={() => {
                  onPetitionSelect(petition);
                  setSearchTerm('');
                  setResults([]);
                }}
                dangerouslySetInnerHTML={{ __html: petition.title.rendered }}
              />
            ))
          ) : (
            <li style={{ padding: '8px 10px', fontStyle: 'italic', color: '#666' }}>
              {__('Aucun résultat', 'amnesty')}
            </li>
          )}
        </ul>
      )}
    </div>
  );
};

const PostSearchControl = ({ categorySlug, onPostSelect, selectedPostLink }) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);
  const [selectedTitle, setSelectedTitle] = useState('');

  const categories = useSelect(
    (select) => select('core').getEntityRecords('taxonomy', 'category', { per_page: 100 }),
    [],
  );

  useEffect(() => {
    if (!searchTerm || !categorySlug) {
      setResults([]);
      return;
    }

    setLoading(true);
    let path;

    if (categorySlug === 'landmark') {
      path = `/wp/v2/landmark?search=${encodeURIComponent(searchTerm)}&per_page=10&_embed`;
    } else if (categorySlug === 'page') {
      path = `/wp/v2/pages?search=${encodeURIComponent(searchTerm)}&per_page=10&_embed`;
    } else {
      const categoryObj = categories?.find((cat) => cat.slug === categorySlug);
      if (!categoryObj) {
        setResults([]);
        setLoading(false);
        return;
      }
      path = `/wp/v2/posts?search=${encodeURIComponent(
        searchTerm,
      )}&category=${categoryObj.id}&per_page=10&_embed`;
    }

    apiFetch({ path })
      .then((data) => {
        setResults(data);
        setLoading(false);
      })
      .catch(() => {
        setResults([]);
        setLoading(false);
      });
  }, [searchTerm, categorySlug, categories]);

  useEffect(() => {
    if (!selectedPostLink) {
      setSelectedTitle('');
    }
  }, [selectedPostLink]);

  return (
    <div>
      <TextControl
        label={__('Rechercher un contenu', 'amnesty')}
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
            maxHeight: 200,
            overflowY: 'auto',
            margin: 0,
            listStyle: 'none',
          }}
        >
          {results.map((post) => {
            const featuredImageUrl = post._embedded?.['wp:featuredmedia']?.[0]?.source_url || '';
            return (
              <li
                key={post.id}
                style={{
                  cursor: 'pointer',
                  padding: '8px 10px',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.75rem',
                  borderBottom: '1px solid #eee',
                }}
                onClick={() => {
                  onPostSelect(post);
                  setSelectedTitle(post.title.rendered);
                  setSearchTerm('');
                  setResults([]);
                }}
              >
                {featuredImageUrl && (
                  <img
                    src={featuredImageUrl}
                    alt={post.title.rendered}
                    style={{
                      width: 40,
                      height: 40,
                      objectFit: 'cover',
                      borderRadius: 2,
                      flexShrink: 0,
                    }}
                  />
                )}
                <div style={{ flexGrow: 1 }}>
                  <strong dangerouslySetInnerHTML={{ __html: post.title.rendered }} />
                </div>
              </li>
            );
          })}
        </ul>
      )}
      {selectedPostLink && selectedTitle && (
        <p style={{ marginTop: '8px', fontStyle: 'italic' }}>
          {__('Contenu sélectionné :', 'amnesty')}{' '}
          <strong dangerouslySetInnerHTML={{ __html: selectedTitle }} />
        </p>
      )}
    </div>
  );
};

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const {
    type,
    surTitle,
    title,
    description,
    imageUrl,
    buttonText,
    buttonLink,
    buttonPosition,
    backgroundColor,
    petitionId,
    petitionData,
    overrideTitle,
    overrideImageUrl,
  } = attributes;

  const blockProps = useBlockProps();
  const [linkCategorySlug, setLinkCategorySlug] = useState('');

  const categories = useSelect(
    (select) => select('core').getEntityRecords('taxonomy', 'category', { per_page: 100 }),
    [],
  );

  const categoryOptions = categories
    ? [
        { label: __('Sélectionnez un type', 'amnesty'), value: '' },
        ...categories
          .filter((cat) => cat.name !== 'Non classé')
          .map((cat) => ({ label: cat.name, value: cat.slug })),
        { label: 'Pages', value: 'page' },
        { label: 'Repères', value: 'landmark' },
      ]
    : [];

  const handlePetitionSelect = (selectedPetition) => {
    if (selectedPetition) {
      let featuredImageUrl = '';
      if (
        selectedPetition._embedded &&
        selectedPetition._embedded['wp:featuredmedia'] &&
        selectedPetition._embedded['wp:featuredmedia'][0]
      ) {
        featuredImageUrl = selectedPetition._embedded['wp:featuredmedia'][0].source_url;
      }

      const completePetitionData = {
        ...selectedPetition,
        featured_media_src_url: featuredImageUrl,
      };

      setAttributes({
        petitionId: selectedPetition.id,
        petitionData: completePetitionData,
        overrideTitle: '',
        overrideImageUrl: '',
      });
    }
  };

  const handlePostLinkSelect = (post) => {
    if (post && post.link) {
      setAttributes({ buttonLink: post.link });
    }
  };

  const goal = petitionData?.acf?.objectif_signatures || 200000;
  // eslint-disable-next-line
  const current = petitionData?.meta?._amnesty_signature_count || 0;
  const percentage = goal > 0 ? (current / goal) * 100 : 0;
  const endDate = formatDate(petitionData?.acf?.date_de_fin) || '30.06.2025';

  const displayTitle = type === 'petition' ? overrideTitle || petitionData?.title?.rendered : title;
  const displayImage =
    type === 'petition' ? overrideImageUrl || petitionData?.featured_media_src_url : imageUrl;

  return (
    <>
      <InspectorControls>
        <PanelBody>
          <SelectControl
            label={__('Type de bloc', 'amnesty')}
            value={type}
            options={[
              { label: __('Pétition', 'amnesty'), value: 'petition' },
              { label: __('Action', 'amnesty'), value: 'action' },
            ]}
            onChange={(value) => setAttributes({ type: value })}
          />
        </PanelBody>

        {type === 'petition' && (
          <PanelBody title={__('Configuration de la Pétition', 'amnesty')}>
            {!petitionId ? (
              <PetitionSearchControl onPetitionSelect={handlePetitionSelect} />
            ) : (
              <div>
                <p style={{ marginTop: 0, marginBottom: '4px' }}>
                  {__('Pétition sélectionnée :', 'amnesty')}
                </p>
                <strong dangerouslySetInnerHTML={{ __html: petitionData.title.rendered }} />
                <Button
                  isLink
                  isDestructive
                  onClick={() => setAttributes({ petitionId: null, petitionData: null })}
                  style={{ marginLeft: '8px' }}
                >
                  {__('Changer', 'amnesty')}
                </Button>
              </div>
            )}
            {petitionId && (
              <>
                <hr />
                <TextControl
                  label={__('Titre personnalisé', 'amnesty')}
                  value={overrideTitle}
                  onChange={(value) => setAttributes({ overrideTitle: value })}
                  help={__('Laissez vide pour utiliser le titre de la pétition.', 'amnesty')}
                />
                <TextControl
                  label={__('Sur-titre', 'amnesty')}
                  value={surTitle}
                  onChange={(value) => setAttributes({ surTitle: value })}
                />
                <p>{__('Image personnalisée', 'amnesty')}</p>
                <MediaUploadCheck>
                  <MediaUpload
                    onSelect={(media) => setAttributes({ overrideImageUrl: media.url })}
                    allowedTypes={['image']}
                    value={overrideImageUrl}
                    render={({ open }) => (
                      <Button onClick={open} isSecondary>
                        {overrideImageUrl
                          ? __("Changer l'image", 'amnesty')
                          : __('Choisir une image', 'amnesty')}
                      </Button>
                    )}
                  />
                </MediaUploadCheck>
              </>
            )}
          </PanelBody>
        )}

        {type === 'action' && (
          <>
            <PanelBody title={__("Contenu de l'Action", 'amnesty')}>
              <TextControl
                label={__('Titre', 'amnesty')}
                value={title}
                onChange={(value) => setAttributes({ title: value })}
              />
              <TextControl
                label={__('Sur-titre', 'amnesty')}
                value={surTitle}
                onChange={(value) => setAttributes({ surTitle: value })}
              />
              <TextareaControl
                label={__('Description', 'amnesty')}
                value={description}
                onChange={(value) => setAttributes({ description: value })}
              />
              <p>{__('Image', 'amnesty')}</p>
              <MediaUploadCheck>
                <MediaUpload
                  onSelect={(media) => setAttributes({ imageUrl: media.url })}
                  allowedTypes={['image']}
                  value={imageUrl}
                  render={({ open }) => (
                    <Button onClick={open} isSecondary>
                      {imageUrl
                        ? __("Changer l'image", 'amnesty')
                        : __('Choisir une image', 'amnesty')}
                    </Button>
                  )}
                />
              </MediaUploadCheck>
              <SelectControl
                label={__('Couleur de fond', 'amnesty')}
                value={backgroundColor}
                options={[
                  { label: 'Jaune', value: 'primary' },
                  { label: 'Blanc', value: 'white' },
                ]}
                onChange={(value) => setAttributes({ backgroundColor: value })}
              />
            </PanelBody>
            <PanelBody title={__('Paramètres du bouton', 'amnesty')}>
              <TextControl
                label={__('Texte du bouton', 'amnesty')}
                value={buttonText}
                onChange={(value) => setAttributes({ buttonText: value })}
              />
              <SelectControl
                label={__('Position du bouton', 'amnesty')}
                value={buttonPosition}
                options={[
                  { label: __('Gauche', 'amnesty'), value: 'left' },
                  { label: __('Centre', 'amnesty'), value: 'center' },
                  { label: __('Droite', 'amnesty'), value: 'right' },
                ]}
                onChange={(value) => setAttributes({ buttonPosition: value })}
              />
              <SelectControl
                label={__('Lier vers un type de contenu', 'amnesty')}
                value={linkCategorySlug}
                options={categoryOptions}
                onChange={(slug) => {
                  setLinkCategorySlug(slug);
                  setAttributes({ buttonLink: '' });
                }}
              />
              {linkCategorySlug && (
                <PostSearchControl
                  categorySlug={linkCategorySlug}
                  onPostSelect={handlePostLinkSelect}
                  selectedPostLink={buttonLink}
                />
              )}
              {buttonLink && (
                <div style={{ marginTop: '1rem' }}>
                  <p style={{ marginTop: 0, marginBottom: '4px', fontSize: '12px' }}>
                    {__('Lien actuel :', 'amnesty')}
                  </p>
                  <a
                    href={buttonLink}
                    target="_blank"
                    rel="noopener noreferrer"
                    style={{ wordBreak: 'break-all', fontSize: '12px' }}
                  >
                    {buttonLink}
                  </a>
                  <Button
                    isLink
                    isDestructive
                    style={{ marginLeft: '8px' }}
                    onClick={() => setAttributes({ buttonLink: '' })}
                  >
                    {__('Retirer', 'amnesty')}
                  </Button>
                </div>
              )}
            </PanelBody>
          </>
        )}
      </InspectorControls>

      <div
        {...blockProps}
        className={classnames(blockProps.className, 'action-card', type, {
          [backgroundColor]: type === 'action' && backgroundColor,
        })}
      >
        <div className="header">
          <div className="title-wrapper">
            <p className="title">{displayTitle || __('Titre', 'amnesty')}</p>
          </div>
          <div className="container-image">
            {displayImage && <img className="action-image" src={displayImage} alt="" />}
          </div>
          {surTitle && (
            <div className="surtitle-wrapper">
              <p className="surtitle">{surTitle || __('Sur-titre', 'amnesty')}</p>
            </div>
          )}
        </div>
        <div className="content">
          {type === 'petition' && petitionData && (
            <div className="petition-content">
              <div className="infos">
                <p className="end-date">
                  {__("Jusqu'au", 'amnesty')} {endDate}
                </p>
                <div className="progress-bar-container">
                  <div className="progress-bar" style={{ width: `${percentage}%` }}></div>
                </div>
                <p className="supports">
                  {`${current.toLocaleString('fr-FR')} ${
                    current > 1 ? __(' soutiens.', 'amnesty') : __(' soutien.', 'amnesty')
                  }`}
                  <span className="help-us">
                    {__('Aidez-nous à atteindre', 'amnesty')} {goal.toLocaleString('fr-FR')}
                  </span>
                </p>
              </div>
              <CustomButton
                label={__('Signer la pétition', 'amnesty')}
                size={'medium'}
                icon={'arrow-right'}
                link={petitionData?.link}
                style={'bg-yellow'}
                alignment={'left'}
              />
            </div>
          )}
          {type === 'action' && (
            <div className="action-content">
              <p className="description">{description || "Description de l'action"}</p>
              <CustomButton
                label={buttonText}
                size={'medium'}
                icon={'zoom-in'}
                link={buttonLink}
                style={'outline-black'}
                alignment={buttonPosition}
              />
            </div>
          )}
        </div>
      </div>
    </>
  );
};

export default EditComponent;
