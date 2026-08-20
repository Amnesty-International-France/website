const { InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { Button, PanelBody } = wp.components;
const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { addFilter } = wp.hooks;
const { __ } = wp.i18n;

const normaliseImage = (media) => ({
  id: media.id,
  url: media.url || media.source_url,
  alt: media.alt || media.alt_text || '',
  width: media.width,
  height: media.height,
});

const renderMediaControl = (label, attribute, value, setAttributes) => (
  <div className="image-editor-control">
    <p className="image-editor-control-label">{label}</p>
    <MediaUploadCheck>
      <MediaUpload
        onSelect={(media) => setAttributes({ [attribute]: normaliseImage(media) })}
        allowedTypes={['image']}
        value={value?.id}
        render={({ open }) => (
          <Button onClick={open} isPrimary>
            {value?.id ? __('Changer l’image', 'amnesty') : __('Ajouter une image', 'amnesty')}
          </Button>
        )}
      />
    </MediaUploadCheck>
    {value?.id && (
      <Button isSecondary isDestructive onClick={() => setAttributes({ [attribute]: {} })}>
        {__('Retirer l’image', 'amnesty')}
      </Button>
    )}
  </div>
);

addFilter('blocks.registerBlockType', 'amnesty-core/image-compare-attributes', (settings, name) => {
  if (name !== 'jetpack/image-compare') {
    return settings;
  }

  return {
    ...settings,
    attributes: {
      ...settings.attributes,
      imageBeforeMobile: {
        type: 'object',
        default: {},
      },
      imageAfterMobile: {
        type: 'object',
        default: {},
      },
    },
  };
});

const withImageCompareMobileControls = createHigherOrderComponent(
  (BlockEdit) => {
    const ImageCompareMobileControls = (props) => {
      if (props.name !== 'jetpack/image-compare') {
        return <BlockEdit {...props} />;
      }

      const { attributes, setAttributes } = props;
      const { imageBeforeMobile = {}, imageAfterMobile = {} } = attributes;

      return (
        <Fragment>
          <BlockEdit {...props} />
          <InspectorControls>
            <PanelBody title={__('Images mobiles', 'amnesty')} initialOpen={false}>
              {renderMediaControl(
                __('Image avant mobile', 'amnesty'),
                'imageBeforeMobile',
                imageBeforeMobile,
                setAttributes,
              )}
              {renderMediaControl(
                __('Image après mobile', 'amnesty'),
                'imageAfterMobile',
                imageAfterMobile,
                setAttributes,
              )}
            </PanelBody>
          </InspectorControls>
        </Fragment>
      );
    };

    return ImageCompareMobileControls;
  },
  'withImageCompareMobileControls',
);

addFilter('editor.BlockEdit', 'amnesty-core/image-compare-mobile-controls', withImageCompareMobileControls);
