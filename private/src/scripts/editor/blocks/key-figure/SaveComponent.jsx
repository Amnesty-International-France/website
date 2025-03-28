const { useBlockProps } = wp.blockEditor;

const SaveComponent = ({ attributes }) => (
  <div {...useBlockProps.save()} className="key-figure">
    <p className="title">{attributes.title}</p>
    <p className="text">{attributes.text}</p>
  </div>
);

export default SaveComponent;
