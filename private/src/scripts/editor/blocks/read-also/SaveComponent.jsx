import './style.scss';

const { useBlockProps } = wp.blockEditor;
const { __ } = wp.i18n;

const SaveComponent = (attributes) => (
  <div {...useBlockProps.save()} className="read-also-block">
    <p>
      {__('À lire aussi', 'amnesty')}:
      <a href={attributes.link} target="_blank" rel="noopener noreferrer">
        {attributes.text || __('Sélectionnez un post', 'amnesty')}
      </a>
    </p>
  </div>
);

export default SaveComponent;
