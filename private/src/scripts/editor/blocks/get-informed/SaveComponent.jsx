import ArrowRight from './icons/ArrowRight.jsx';
import Folder from './icons/Folder.jsx';
import PinMap from './icons/PinMap.jsx';
import Play from './icons/Play.jsx';
import Plus from './icons/Plus.jsx';

const { __ } = wp.i18n;
const { useBlockProps } = wp.blockEditor;

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

const SaveComponent = ({ attributes }) => {
  const { links = [] } = attributes;

  return (
    <div {...useBlockProps.save()} className="get-informed">
      <div className="content">
        <h3 className="title">{__("S'informer", 'amnesty')}</h3>

        {links.length === 0 && <p>{__('Aucun lien ajouté.', 'amnesty')}</p>}

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
  );
};

export default SaveComponent;
