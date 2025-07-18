const { __ } = wp.i18n;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl } = wp.components;

/**
 * Extract YouTube video ID from various URL formats.
 */
const getYouTubeEmbedUrl = (url) => {
  try {
    const parsedUrl = new URL(url);

    if (parsedUrl.hostname === 'youtu.be') {
      return `https://www.youtube.com/embed/${parsedUrl.pathname.slice(1)}`;
    }

    if (parsedUrl.hostname.includes('youtube.com') && parsedUrl.searchParams.has('v')) {
      const videoId = parsedUrl.searchParams.get('v');
      return `https://www.youtube.com/embed/${videoId}`;
    }

    return url;
  } catch (e) {
    return url;
  }
};

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const { url, title } = attributes;

  const onURLChange = (u) => {
    setAttributes({ url: u });
  };

  const onTitleChange = (value) => {
    setAttributes({ title: value });
  };

  const embedUrl = getYouTubeEmbedUrl(url);

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres de la vidéo', 'amnesty')}>
          <TextControl
            label="URL de la vidéo (YouTube, Vimeo, MP4...)"
            value={url}
            onChange={onURLChange}
            placeholder="https://..."
            style={{ textTransform: 'initial' }}
          />
          <TextControl
            label="Titre de la vidéo"
            value={title}
            onChange={onTitleChange}
            style={{ textTransform: 'initial' }}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className="video-block">
        {url ? (
          <div className="video-wrapper">
            <iframe
              src={embedUrl}
              width="100%"
              frameBorder="0"
              allow="autoplay; encrypted-media"
              allowFullScreen
              style={{ pointerEvents: 'none' }}
            />
          </div>
        ) : (
          <p>Aucune vidéo sélectionnée.</p>
        )}
        {title && (
          <p className="video-title">
            <span className="video-label">Vidéo : </span>
            {title}
          </p>
        )}
      </div>
    </>
  );
};

export default EditComponent;
