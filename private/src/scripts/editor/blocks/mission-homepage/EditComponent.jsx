import CustomButton from '../button/Button.jsx';
import InvestigateIcon from './icons/InvestigateIcon.jsx';
import AlertIcon from './icons/AlertIcon.jsx';
import ActionIcon from './icons/ActionIcon.jsx';

const { useBlockProps } = wp.blockEditor;

const missionItems = [
  {
    title: 'Enquêter',
    description:
      'Chaque jour, nos équipes de recherche se rendent sur le terrain pour enquêter sur les violations des droits humains et recueillir des témoignages et des preuves.',
    IconComponent: InvestigateIcon,
  },
  {
    title: 'Alerter',
    description:
      'Nous disposons ainsi d’une information inédite qui permet d’alerter les médias et l’opinion publique',
    IconComponent: AlertIcon,
  },
  {
    title: 'Agir',
    description:
      'Nous exerçons des pressions sur les décideurs via un travail de plaidoyer et des campagnes qui mobilisent (pétitions, courriers aux autorités, messages, débats...)',
    IconComponent: ActionIcon,
  },
];

const EditComponent = () => {
  const blockProps = useBlockProps();

  return (
    <section {...blockProps} className="mission-homepage">
      <div className="mission-homepage-wrapper">
        <h2 className="title">Notre mission</h2>
        <h3 className="subtitle">On se bat ensemble, on gagne ensemble.</h3>
        <p className="chapo">Notre combat pour les droits humains repose sur 3 piliers :</p>
        <div className="items">
          {missionItems.map((item) => (
            <div key={item.title} className="item">
              <p className="item-title">{item.title}</p>
              <div className="icon-container">
                <item.IconComponent />
              </div>
              <p className="description">{item.description}</p>
            </div>
          ))}
        </div>
        <CustomButton
          label="Nous connaître"
          size="medium"
          icon="arrow-right"
          link="/nous-connaitre"
          alignment="center"
          style="outline-yellow"
        />
      </div>
    </section>
  );
};

export default EditComponent;
