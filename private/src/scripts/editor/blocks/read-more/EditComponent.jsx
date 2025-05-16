const { useState } = wp.element;
const { useBlockProps, InnerBlocks } = wp.blockEditor;

const EditComponent = () => {
  const blockProps = useBlockProps();
  const [isExpanded, setIsExpanded] = useState(false);

  const handleToggle = () => {
    setIsExpanded(!isExpanded);
  };

  return (
    <div {...blockProps} className="read-more-block">
      <div className="read-more-toggle" onClick={handleToggle}>
        <div className="icon-container">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="15"
            height="10"
            viewBox="0 0 15 10"
            fill="currentColor"
            className={isExpanded ? 'rotated' : ''}
          >
            <path
              fillRule="evenodd"
              clipRule="evenodd"
              d="M7.03859 6.3641L12.5133 0L14.0772 1.81795L7.03859 10L0 1.81795L1.56389 0L7.03859 6.3641Z"
              fill="#FFFF00"
            />
          </svg>
        </div>
        <span className="label">{isExpanded ? 'Lire moins' : 'Lire la suite'}</span>
      </div>

      <div className={`read-more-content ${isExpanded ? 'expanded' : 'collapsed'}`}>
        <InnerBlocks />
      </div>
    </div>
  );
};

export default EditComponent;
