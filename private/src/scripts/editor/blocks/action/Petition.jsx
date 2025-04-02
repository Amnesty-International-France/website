import ProgressBar from './ProgressBar.jsx';

const Petition = ({ progress, button }) => (
  <div className="petition-content">
    <div className="infos">
      <p className="end-date">Jusqu&apos;au 30.06.2025</p>
      <ProgressBar progress={progress} />
      <p className="supports">
        150366 soutiens. <span className="help-us">Aidez-nous à atteindre 200000</span>
      </p>
    </div>
    {button}
  </div>
);

export default Petition;
