const { __ } = wp.i18n;
const { TextControl, Button } = wp.components;
const { MediaUpload, MediaUploadCheck } = wp.blockEditor;

const ExternalPostControl = ({ item, updateItem }) => (
  <>
    <TextControl
      label={__('Titre', 'amnesty')}
      value={item.externalItemTitle}
      onChange={(val) => updateItem('externalItemTitle', val)}
    />

    <TextControl
      label={__('Lien', 'amnesty')}
      value={item.externalItemLink}
      onChange={(val) => updateItem('externalItemLink', val)}
    />

    <MediaUploadCheck>
      <MediaUpload
        onSelect={(media) => {
          updateItem({
            externalItemImgId: media.id,
            externalItemImgUrl: media.url,
          });
        }}
        allowedTypes={['image']}
        value={item.externalItemImgId}
        render={({ open }) => (
          <div style={{ marginTop: '10px' }}>
            <Button onClick={open} isSecondary className="is-primary">
              {item.externalItemImgUrl
                ? __('Changer l’image', 'amnesty')
                : __('Sélectionner une image', 'amnesty')}
            </Button>
          </div>
        )}
      />
    </MediaUploadCheck>
  </>
);

export default ExternalPostControl;
