const { useBlockProps } = wp.blockEditor;
const { __ } = wp.i18n;

const SaveComponent = ({ attributes }) => {
  const { title, files } = attributes;

  return (
    <div {...useBlockProps.save()} className="download-go-further-block">
      {title && (
        <div className="title-container">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            strokeWidth="1.5"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"
            />
          </svg>

          <h3 className="title">{title}</h3>
        </div>
      )}
      {files && files.length > 0 && (
        <ul className="list">
          {files.map((file) => (
            <li key={file.id} className="item">
              <p className="item-text">
                {file.name} ({file.type}, {file.size})
              </p>
              <a href={file.url} download>
                <button className="item-button">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    className="size-6"
                  >
                    <path
                      fillRule="evenodd"
                      d="M19.5 21a3 3 0 0 0 3-3V9a3 3 0 0 0-3-3h-5.379a.75.75 0 0 1-.53-.22L11.47 3.66A2.25 2.25 0 0 0 9.879 3H4.5a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h15Zm-6.75-10.5a.75.75 0 0 0-1.5 0v4.19l-1.72-1.72a.75.75 0 0 0-1.06 1.06l3 3a.75.75 0 0 0 1.06 0l3-3a.75.75 0 1 0-1.06-1.06l-1.72 1.72V10.5Z"
                      clipRule="evenodd"
                    />
                  </svg>
                  {__('Télécharger', 'amnesty')}
                </button>
              </a>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
};

export default SaveComponent;
