import Button from './Button.jsx';

const { useBlockProps } = wp.blockEditor;

const SaveComponent = ({ attributes }) => {
  const { label, size, style, icon, link, alignment } = attributes;

  return (
    <div {...useBlockProps.save()}>
      <Button
        label={label}
        size={size}
        style={style}
        icon={icon}
        link={link}
        alignment={alignment}
      />
    </div>
  );
};

export default SaveComponent;
