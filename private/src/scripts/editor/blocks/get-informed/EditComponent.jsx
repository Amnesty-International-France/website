import ArrowRight from './icons/ArrowRight.jsx';
import Folder from './icons/Folder.jsx';
import PinMap from './icons/PinMap.jsx';
import Play from './icons/Play.jsx';
import Plus from './icons/Plus.jsx';

const { __ } = wp.i18n;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, Button, TextControl, SelectControl } = wp.components;
const { Fragment } = wp.element;
const { cloneDeep } = lodash;

const LINK_ICONS = {
  dossier: <Folder />,
  pays: <PinMap />,
  combat: <Plus />,
  video: <Play />,
  libre: <ArrowRight />,
};

const LINK_LABELS = {
  dossier: 'DÉCOUVRIR LE DOSSIER COMPLET',
  pays: 'EN APPRENDRE PLUS SUR LE PAYS',
  combat: 'EN APPRENDRE PLUS SUR LE COMBAT',
  video: 'VOIR UNE VIDÉO SUR LE SUJET',
  libre: 'LABEL PERSONNALISE',
};

const EditComponent = ({ attributes, setAttributes }) => {
  const { links = [] } = attributes;

  const updateLink = (index, field, value) => {
    const newLinks = cloneDeep(links);
    newLinks[index][field] = value;
    setAttributes({ links: newLinks });
  };

  const addLink = () => {
    setAttributes({
      links: [...links, { type: '', title: '', url: '', customLabel: '' }],
    });
  };

  const removeLink = (index) => {
    const newLinks = [...links];
    newLinks.splice(index, 1);
    setAttributes({ links: newLinks });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Liens à afficher', 'amnesty')}>
          {links.map((link, index) => (
            <Fragment key={index}>
              <h4>
                {__('Lien', 'amnesty')} {index + 1}
              </h4>

              <SelectControl
                label={__('Type de lien', 'amnesty')}
                value={link.type}
                options={[
                  { label: __('Choisir un type…', 'amnesty'), value: '' },
                  { label: 'Dossier', value: 'dossier' },
                  { label: 'Pays', value: 'pays' },
                  { label: 'Combat', value: 'combat' },
                  { label: 'Video', value: 'video' },
                  { label: 'Libre', value: 'libre' },
                ]}
                onChange={(value) => updateLink(index, 'type', value)}
              />

              {link.type === 'libre' && (
                <TextControl
                  label={__('Texte du label', 'amnesty')}
                  value={link.customLabel}
                  onChange={(value) => updateLink(index, 'customLabel', value)}
                />
              )}

              <TextControl
                label={__('Titre du lien', 'amnesty')}
                value={link.title}
                onChange={(value) => updateLink(index, 'title', value)}
              />

              <TextControl
                label={__('URL du lien', 'amnesty')}
                value={link.url}
                onChange={(value) => updateLink(index, 'url', value)}
              />

              <Button
                isDestructive
                onClick={() => removeLink(index)}
                style={{ marginBottom: '1rem' }}
              >
                {__('Supprimer ce lien', 'amnesty')}
              </Button>
            </Fragment>
          ))}

          <Button isPrimary onClick={addLink}>
            {__('Ajouter un lien', 'amnesty')}
          </Button>
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className="get-informed">
        <div className="content">
          <h3 className="title">S&apos;informer</h3>

          {links.length === 0 && <p>{__('Aucun lien ajouté pour l’instant.', 'amnesty')}</p>}

          <div className="links">
            {links.map((link, index) => {
              const icon = LINK_ICONS[link.type] || null;
              const label = link.type === 'libre' ? link.customLabel : LINK_LABELS[link.type] || '';

              return (
                <div className="link-item" key={index}>
                  <div className="link-meta">
                    <div className="link-icon-container">
                      <span className="link-icon">{icon}</span>
                    </div>
                    <span className="link-label">{label}</span>
                  </div>

                  {link.title && link.url && (
                    <a
                      className="link"
                      href={link.url}
                      {...(link.type === 'video'
                        ? {
                            target: '_blank',
                            rel: 'noopener noreferrer',
                          }
                        : {})}
                    >
                      {link.title}
                    </a>
                  )}
                </div>
              );
            })}
          </div>
        </div>
      </div>
    </>
  );
};

export default EditComponent;
