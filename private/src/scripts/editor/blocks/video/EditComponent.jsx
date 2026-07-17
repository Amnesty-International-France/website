const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, TextControl, Button } = wp.components;

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

/**
 * Detect a self-hosted video file (mp4, webm, ...) from its URL.
 */
const isSelfHostedVideo = (url) => {
  try {
    const { pathname } = new URL(url);
    return /\.(mp4|webm|ogv|ogg|mov|m4v)$/i.test(pathname);
  } catch (e) {
    return false;
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

  const onSelectMedia = (media) => {
    setAttributes({ url: media.url });
  };

  const selfHosted = isSelfHostedVideo(url);
  const embedUrl = getYouTubeEmbedUrl(url);

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres de la vidéo', 'amnesty')}>
          <MediaUploadCheck>
            <MediaUpload
              onSelect={onSelectMedia}
              allowedTypes={['video']}
              render={({ open }) => (
                <Button onClick={open} isPrimary style={{ marginBottom: '12px' }}>
                  {url
                    ? __('Changer la vidéo (médiathèque)', 'amnesty')
                    : __('Choisir une vidéo (médiathèque)', 'amnesty')}
                </Button>
              )}
            />
          </MediaUploadCheck>
          <TextControl
            label="URL de la vidéo (YouTube ou fichier MP4)"
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
            {selfHosted ? (
              <video src={url} width="100%" controls style={{ pointerEvents: 'none' }} />
            ) : (
              <iframe
                src={embedUrl}
                width="100%"
                frameBorder="0"
                allow="autoplay; encrypted-media"
                allowFullScreen
                style={{ pointerEvents: 'none' }}
              />
            )}
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
