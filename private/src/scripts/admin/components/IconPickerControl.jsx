import Icon from '../../editor/components/Icon.jsx';

const { useState } = wp.element;
const { __ } = wp.i18n;
const { Button, Modal } = wp.components;

const reqSvgs = require.context('../../editor/icons', false, /\.svg$/);
const iconOptions = reqSvgs.keys().map((filePath) => {
  const fileName = filePath.replace('./', '').replace('.svg', '');
  return {
    label: fileName,
    value: fileName,
  };
});

const IconPickerControl = ({ label, value, onChange }) => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const openModal = () => setIsModalOpen(true);
  const closeModal = () => setIsModalOpen(false);

  return (
    <div className="components-base-control icon-picker-control">
      <div className="components-base-control__field">
        {label && (
          <label htmlFor="icon-picker-control-btn" className="components-base-control__label">
            {label}
          </label>
        )}
        <Button
          id="icon-picker-control-btn"
          className="icon-picker-control__trigger"
          variant="secondary"
          onClick={openModal}
        >
          <Icon name={value} className="icon-picker-control__trigger-icon" />
          <span>{value}</span>
        </Button>
      </div>

      {isModalOpen && (
        <Modal
          className="icon-picker-modal"
          title={__('Choisir une icône', 'amnesty')}
          onRequestClose={closeModal}
        >
          <div className="icon-picker-grid">
            {iconOptions.map((opt) => (
              <Button
                key={opt.value}
                variant={value === opt.value ? 'primary' : 'secondary'}
                onClick={() => {
                  onChange(opt.value);
                  closeModal();
                }}
                title={opt.label}
              >
                <Icon name={opt.value} className="icon-picker-grid__icon" />
              </Button>
            ))}
          </div>
        </Modal>
      )}
    </div>
  );
};

export default IconPickerControl;
