const { __ } = wp.i18n;
const { useBlockProps, InnerBlocks, RichText, InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl } = wp.components;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const { title, showTitle } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du titre', 'amnesty')}>
          <ToggleControl
            label={__('Afficher le titre', 'amnesty')}
            checked={showTitle}
            onChange={(value) => setAttributes({ showTitle: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className="small-section-block">
        <div className="small-section-block-content">
          {showTitle && (
            <RichText
              tagName="h3"
              className="small-section-block-content-title"
              value={title}
              placeholder={__('Saisissez un titre…', 'amnesty')}
              onChange={(value) => setAttributes({ title: value })}
            />
          )}
          <div className="small-section-block-inner-blocks-container">
            <InnerBlocks
              allowedBlocks={[
                'core/list',
                'core/paragraph',
                'core/button',
                'core/columns',
                'amnesty-core/key-figure',
              ]}
            />
          </div>
        </div>
      </div>
    </>
  );
};

export default EditComponent;
