const ProgressBar = ({ progress }) => (
  <div className="progress-bar-container">
    <div className="progress-bar" style={{ width: `${progress}%` }}></div>
  </div>
);

export default ProgressBar;
