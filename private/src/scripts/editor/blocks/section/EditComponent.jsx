import classnames from 'classnames';

const { __ } = wp.i18n;
const { useBlockProps, InnerBlocks, RichText, InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl, SelectControl } = wp.components;

const EditComponent = (props) => {
  const { attributes, setAttributes } = props;
  const { sectionSize, title, showTitle, fullWidth, contentSize, backgroundColor } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres de la section', 'amnesty')}>
          <SelectControl
            label={__('Taille de la section', 'amnesty')}
            value={sectionSize}
            options={[
              { label: __('Petite', 'amnesty'), value: 'small' },
              { label: __('Grande', 'amnesty'), value: 'large' },
            ]}
            onChange={(value) => setAttributes({ sectionSize: value })}
          />
          <ToggleControl
            label={__('Afficher le titre', 'amnesty')}
            checked={showTitle}
            onChange={(value) => setAttributes({ showTitle: value })}
          />
          {sectionSize === 'large' && (
            <>
              <ToggleControl
                label={__('Full largeur', 'amnesty')}
                checked={fullWidth}
                onChange={(value) => setAttributes({ fullWidth: value })}
              />
              <SelectControl
                label={__('Couleur de fond', 'amnesty')}
                value={backgroundColor}
                options={[
                  { label: __('Blanc', 'amnesty'), value: 'white' },
                  { label: __('Gris', 'amnesty'), value: 'grey' },
                  { label: __('Noir', 'amnesty'), value: 'black' },
                ]}
                onChange={(value) => setAttributes({ backgroundColor: value })}
              />
              <SelectControl
                label={__('Taille du contenu', 'amnesty')}
                value={contentSize}
                options={[
                  { label: __('SM', 'amnesty'), value: 'sm' },
                  { label: __('MD', 'amnesty'), value: 'md' },
                  { label: __('LG', 'amnesty'), value: 'lg' },
                  { label: __('XL', 'amnesty'), value: 'xl' },
                ]}
                onChange={(value) => setAttributes({ contentSize: value })}
              />
            </>
          )}
        </PanelBody>
      </InspectorControls>

      <div
        {...useBlockProps()}
        className={classnames('section-block', sectionSize, backgroundColor, {
          'full-width': fullWidth,
        })}
      >
        <div className="section-block-content">
          {showTitle && (
            <RichText
              tagName="h3"
              className="section-block-content-title"
              value={title}
              placeholder={__('Saisissez un titre…', 'amnesty')}
              onChange={(value) => setAttributes({ title: value })}
            />
          )}
          <div className={classnames('section-block-inner-blocks-container', contentSize)}>
            <InnerBlocks
              allowedBlocks={[
                'core/heading',
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
