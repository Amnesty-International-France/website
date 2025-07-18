const { __ } = wp.i18n;
const { useSelect } = wp.data;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, Button, TextControl } = wp.components;

const EditComponent = ({ attributes, setAttributes }) => {
  const { title, fileIds = [] } = attributes;

  const files = useSelect((select) => fileIds.map((id) => select('core').getMedia(id)), [fileIds]);

  const updateTitle = (newTitle) => {
    setAttributes({ title: newTitle });
  };

  const allowedMimeTypes = [
    'image/jpeg',
    'image/pjpeg',
    'image/png',
    'image/gif',
    'image/bmp',
    'image/x-ms-bmp',
    'image/webp',
    'application/pdf',
    'application/msword',
    'application/vnd.ms-excel',
    'application/vnd.ms-word',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.openxmlformats.officedocument.spreadsheetml.sheet',
    'application/vnd.oasis.opendocument.spreadsheet',
    'application/vnd.oasis.opendocument.text',
    'application/zip',
    'audio/mpeg',
    'audio/mp3',
    'audio/ogg',
    'audio/wav',
    'video/mp4',
    'video/mpeg',
    'video/quicktime',
    'text/plain',
  ];

  const getHumanReadableFileType = (mimeType) => {
    switch (mimeType) {
      case 'image/jpeg':
      case 'image/pjpeg':
        return 'JPEG';
      case 'image/png':
        return 'PNG';
      case 'image/gif':
        return 'GIF';
      case 'image/bmp':
      case 'image/x-ms-bmp':
        return 'BMP';
      case 'image/webp':
        return 'WebP';
      case 'application/pdf':
        return 'PDF';
      case 'application/msword':
      case 'application/vnd.ms-word':
        return 'Document Word (DOC)';
      case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
        return 'Document Word (DOCX)';
      case 'application/vnd.ms-excel':
      case 'application/vnd.openxmlformats.officedocument.spreadsheetml.sheet':
        return 'Feuille de calcul Excel';
      case 'application/vnd.oasis.opendocument.spreadsheet':
        return 'Feuille de calcul OpenDocument';
      case 'application/vnd.oasis.opendocument.text':
        return 'Document OpenDocument Text';
      case 'audio/mpeg':
      case 'audio/mp3':
        return 'Fichier audio MP3';
      case 'audio/ogg':
        return 'Fichier audio OGG';
      case 'audio/wav':
        return 'Fichier audio WAV';
      case 'video/mp4':
        return 'Vidéo MP4';
      case 'video/mpeg':
        return 'Vidéo MPEG';
      case 'video/quicktime':
        return 'Vidéo QuickTime';
      case 'text/plain':
        return 'Fichier texte';
      case 'application/zip':
        return 'Archive ZIP';
      default:
        return 'Fichier';
    }
  };

  const addFile = (file) => {
    if (!allowedMimeTypes.includes(file.mime)) {
      alert(
        __(
          "Ce type de fichier n'est pas autorisé. Seuls les fichiers PDF, DOCX, XLSX, MP3, etc., sont autorisés.",
          'amnesty',
        ),
      );
      return;
    }

    const newIds = [...fileIds, file.id];
    setAttributes({ fileIds: newIds });
  };

  const removeFile = (id) => {
    const newIds = fileIds.filter((fid) => fid !== id);
    setAttributes({ fileIds: newIds });
  };

  const formatFileSize = (file) => {
    const size = file?.media_details?.filesize || 0;
    return `${(size / 1024).toFixed(2)} kb`;
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du bloc', 'amnesty')} initialOpen={true}>
          <TextControl
            label={__('Titre', 'amnesty')}
            value={title}
            onChange={updateTitle}
            placeholder={__('Entrez un titre…', 'amnesty')}
          />
          <MediaUploadCheck>
            <MediaUpload
              onSelect={addFile}
              allowedTypes={allowedMimeTypes}
              render={({ open }) => (
                <Button onClick={open} variant="primary">
                  {__('Ajouter un fichier', 'amnesty')}
                </Button>
              )}
            />
          </MediaUploadCheck>
          <ul>
            {files.map(
              (file) =>
                file && (
                  <li key={file.id}>
                    <span>
                      {file.title?.rendered || file.slug} (
                      {getHumanReadableFileType(file.mime_type)}, {formatFileSize(file)})
                    </span>
                    <Button onClick={() => removeFile(file.id)} isSecondary>
                      {__('Supprimer', 'amnesty')}
                    </Button>
                  </li>
                ),
            )}
          </ul>
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className="download-go-further-block">
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
        {files.length > 0 && (
          <ul className="list">
            {files.map(
              (file) =>
                file && (
                  <li key={file.id} className="item">
                    <a className="item-link" href={file.source_url} download>
                      <p className="item-text">
                        {file.title?.rendered || file.slug} (
                        {getHumanReadableFileType(file.mime_type)}, {formatFileSize(file)})
                      </p>
                    </a>
                    <a href={file.source_url} download>
                      <button className="item-button">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 24 24"
                          fill="currentColor"
                        >
                          <path
                            fillRule="evenodd"
                            d="M19.5 21a3 3 0 0 0 3-3V9a3 3 0 0 0-3-3h-5.379a.75.75 0 0 1-.53-.22L11.47 3.66A2.25 2.25 0 0 0 9.879 3H4.5a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h15Zm-6.75-10.5a.75.75 0 0 0-1.5 0v4.19l-1.72-1.72a.75.75 0 0 0-1.06 1.06l3 3a.75.75 0 0 0 1.06 0l3-3a.75.75 0 1 0-1.06-1.06l-1.72 1.72V10.5Z"
                            clipRule="evenodd"
                          />
                        </svg>
                        <span className="item-button-label">{__('Télécharger', 'amnesty')}</span>
                      </button>
                    </a>
                  </li>
                ),
            )}
          </ul>
        )}
      </div>
    </>
  );
};

export default EditComponent;
