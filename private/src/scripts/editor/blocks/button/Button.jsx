import classnames from 'classnames';
import ArrowLeft from './icons/ArrowLeft.jsx';
import ArrowRight from './icons/ArrowRight.jsx';
import Pencil from './icons/Pencil.jsx';
import ZoomIn from './icons/ZoomIn.jsx';

const Button = ({ label, size, style, icon, link, alignment, className, customId }) => (
  <div className={classnames('custom-button-block', alignment, className)}>
    <a
      id={customId}
      href={link}
      target="_blank"
      rel="noopener noreferrer"
      className="custom-button"
    >
      <div className={classnames('content', size, style)}>
        {icon && (
          <div className="icon-container">
            {icon === 'arrow-left' && <ArrowLeft />}
            {icon === 'arrow-right' && <ArrowRight />}
            {icon === 'pencil' && <Pencil />}
            {icon === 'zoom-in' && <ZoomIn />}
          </div>
        )}
        <div className="button-label">{label}</div>
      </div>
    </a>
  </div>
);

export default Button;
