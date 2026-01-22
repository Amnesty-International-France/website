import classnames from 'classnames';
import CustomButton from '../button/Button.jsx';
import PostSearchControl from '../../components/PostSearchControl.jsx';

const { __ } = wp.i18n;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl, SelectControl, ToggleControl, Button: WpButton } = wp.components;

const EditComponent = ({ attributes, setAttributes }) => {
  const {
    direction,
    title,
    subTitle,
    buttonLabel,
    linkType,
    internalUrl,
    externalUrl,
    internalUrlTitle,
    targetBlank,
    attrs,
  } = attributes;

  const allowedTypesForThisBlock = [
    'post',
    'pages',
    'fiche_pays',
    'landmark',
    'local-structures',
    'petition',
    'press-release',
    'training',
    'document',
    'edh',
    'chronique',
    'tribe_events',
  ];

  const handlePostSelect = (post) => {
    if (post) {
      setAttributes({
        internalUrl: post.link,
        internalUrlTitle: post.title.rendered,
        postId: post.id,
      });
    } else {
      setAttributes({
        internalUrl: '',
        internalUrlTitle: '',
        postId: 0,
      });
    }
  };

  const handleRemoveLink = () => {
    setAttributes({ internalUrl: '', internalUrlTitle: '', postId: 0 });
  };

  const finalButtonLink = linkType === 'internal' ? internalUrl : externalUrl;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Contenu du bloc', 'amnesty')} initialOpen={true}>
          <SelectControl
            label={__('Disposition', 'amnesty')}
            value={direction}
            options={[
              { label: __('Horizontal', 'amnesty'), value: 'horizontal' },
              { label: __('Vertical', 'amnesty'), value: 'vertical' },
            ]}
            onChange={(value) => setAttributes({ direction: value })}
          />
          <TextControl
            label={__('Titre', 'amnesty')}
            value={title}
            onChange={(value) => setAttributes({ title: value })}
            placeholder={__('Entrez un titre…', 'amnesty')}
          />
          <TextControl
            label={__('Sous-titre', 'amnesty')}
            value={subTitle}
            onChange={(value) => setAttributes({ subTitle: value })}
            placeholder={__('Entrez un sous-titre…', 'amnesty')}
          />
        </PanelBody>
        <PanelBody title={__('Paramètres du bouton', 'amnesty')}>
          <TextControl
            label={__('Label du bouton', 'amnesty')}
            value={buttonLabel}
            onChange={(value) => setAttributes({ buttonLabel: value })}
            placeholder={__('Label du bouton', 'amnesty')}
          />
          <SelectControl
            label={__('Type de lien', 'amnesty')}
            value={linkType}
            options={[
              { label: 'Lien interne (contenu du site)', value: 'internal' },
              { label: 'Lien externe (URL)', value: 'external' },
            ]}
            onChange={(value) => setAttributes({ linkType: value })}
          />

          {linkType === 'internal' && (
            <>
              {!internalUrl ? (
                <PostSearchControl
                  onPostSelect={handlePostSelect}
                  allowedTypes={allowedTypesForThisBlock}
                />
              ) : (
                <div style={{ marginTop: '10px', paddingTop: '10px', borderTop: '1px solid #ccc' }}>
                  <p style={{ margin: 0 }}>
                    {__('Lien sélectionné :', 'amnesty')}{' '}
                    <strong dangerouslySetInnerHTML={{ __html: internalUrlTitle }} />
                  </p>
                  <WpButton isLink isDestructive onClick={handleRemoveLink}>
                    {__('Retirer le lien', 'amnesty')}
                  </WpButton>
                </div>
              )}
            </>
          )}

          {linkType === 'external' && (
            <TextControl
              label={__('URL du lien externe', 'amnesty')}
              value={externalUrl}
              placeholder="https://exemple.com"
              onChange={(value) => setAttributes({ externalUrl: value })}
            />
          )}

          {(internalUrl || externalUrl) && (
            <ToggleControl
              label={__('Ouvrir dans un nouvel onglet', 'amnesty')}
              checked={!!targetBlank}
              onChange={(value) => setAttributes({ targetBlank: value })}
            />
          )}

          <SelectControl
            label={__('Attribut analytics', 'amnesty')}
            value={attrs}
            options={[
              { label: 'Aucun', value: '' },
              { label: 'Lien faire un don', value: 'don' },
              { label: 'Lien devenir membre', value: 'membre' },
            ]}
            onChange={(value) => setAttributes({ attrs: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className={classnames('call-to-action-block', direction)}>
        <div className="call-to-action-content">
          <p className="title">{title}</p>
          <p className="subTitle">{subTitle}</p>
        </div>
        <CustomButton
          icon="arrow-right"
          label={buttonLabel}
          size="medium"
          link={finalButtonLink}
          style="bg-yellow"
          alignment={direction === 'horizontal' ? 'right' : 'center'}
        />
      </div>
    </>
  );
};

export default EditComponent;
