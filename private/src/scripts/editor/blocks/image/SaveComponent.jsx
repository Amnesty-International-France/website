const { useBlockProps } = wp.blockEditor;

const SaveComponent = ({ attributes }) => {
  const { imageUrl, altText, caption, description } = attributes;

  return (
    <div {...useBlockProps.save()} className="image-block">
      {imageUrl && (
        <>
          <div className="image-wrapper">
            <img src={imageUrl} alt={altText} />
            {caption && <p className="image-caption">{caption}</p>}
          </div>
          {description && <p className="image-description">{description}</p>}
        </>
      )}
    </div>
  );
};

export default SaveComponent;
