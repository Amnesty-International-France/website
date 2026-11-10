import {
  TESTIMONIAL_MAX_LENGTHS,
  remainingCharacters,
  validateTestimonial,
} from '../../clh/testimonial-validation';

const { __, _n, sprintf } = wp.i18n;
const { useBlockProps, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { Button, Card, CardBody, CardHeader, TextControl, TextareaControl } = wp.components;
const { useSelect } = wp.data;

const EMPTY_TESTIMONIAL = {
  year: '',
  name: '',
  country: '',
  activity: '',
  quote: '',
  description: '',
  imageId: 0,
};

const TEXT_FIELDS = [
  { key: 'name', label: __('Nom', 'amnesty'), Control: TextControl },
  { key: 'country', label: __('Pays', 'amnesty'), Control: TextControl },
  { key: 'activity', label: __('Activité', 'amnesty'), Control: TextControl },
  { key: 'quote', label: __('Verbatim', 'amnesty'), Control: TextareaControl },
  { key: 'description', label: __('Description', 'amnesty'), Control: TextareaControl },
];

const fieldHelp = (error, remaining) => {
  if (!error && remaining === null) {
    return null;
  }

  return (
    <>
      {error && <span className="clh-testimonials-editor-error">{error} </span>}
      {remaining !== null &&
        sprintf(
          /* translators: %d: number of characters left */
          _n('%d caractère restant', '%d caractères restants', remaining, 'amnesty'),
          remaining,
        )}
    </>
  );
};

const PhotoPicker = ({ imageId, error, onChange }) => {
  const image = useSelect(
    (select) => (imageId ? select('core').getMedia(imageId) : null),
    [imageId],
  );

  return (
    <div className="clh-testimonials-editor-photo">
      {image && (
        <img src={image.media_details?.sizes?.thumbnail?.source_url || image.source_url} alt="" />
      )}
      <MediaUploadCheck>
        <MediaUpload
          onSelect={(media) => onChange(media.id)}
          allowedTypes={['image']}
          value={imageId}
          render={({ open }) => (
            <Button onClick={open} variant="secondary">
              {imageId ? __('Changer la photo', 'amnesty') : __('Choisir une photo', 'amnesty')}
            </Button>
          )}
        />
      </MediaUploadCheck>
      {error && <p className="clh-testimonials-editor-error">{error}</p>}
    </div>
  );
};

const EditComponent = ({ attributes, setAttributes }) => {
  const { testimonials = [], title, subtitle } = attributes;
  const currentYear = new Date().getFullYear();
  const errors = testimonials.map((testimonial) => validateTestimonial(testimonial, currentYear));
  // The saving locks live in the clh-testimonials-validation editor plugin.
  const isInvalid = errors.some((testimonialErrors) => Object.keys(testimonialErrors).length > 0);

  const updateTestimonial = (index, key, value) => {
    setAttributes({
      testimonials: testimonials.map((testimonial, i) =>
        i === index ? { ...testimonial, [key]: value } : testimonial,
      ),
    });
  };

  const addTestimonial = () => {
    setAttributes({ testimonials: [...testimonials, { ...EMPTY_TESTIMONIAL }] });
  };

  const removeTestimonial = (index) => {
    setAttributes({ testimonials: testimonials.filter((_, i) => i !== index) });
  };

  return (
    <div {...useBlockProps({ className: 'clh-testimonials-editor' })}>
      <TextControl
        label={__('Titre', 'amnesty')}
        value={title}
        onChange={(value) => setAttributes({ title: value })}
      />
      <TextareaControl
        label={__('Sous-titre', 'amnesty')}
        value={subtitle}
        onChange={(value) => setAttributes({ subtitle: value })}
      />
      {isInvalid && (
        <p className="clh-testimonials-editor-error">
          {__('Corrigez les témoignages pour pouvoir enregistrer la page.', 'amnesty')}
        </p>
      )}
      {testimonials.map((testimonial, index) => (
        <Card key={index} className="clh-testimonials-editor-item">
          <CardHeader>
            <strong>
              {sprintf(
                /* translators: %d: position of the testimonial */
                __('Témoignage %d', 'amnesty'),
                index + 1,
              )}
            </strong>
            <Button isDestructive variant="link" onClick={() => removeTestimonial(index)}>
              {__('Supprimer', 'amnesty')}
            </Button>
          </CardHeader>
          <CardBody>
            <TextControl
              label={__('Année', 'amnesty')}
              value={String(testimonial.year ?? '')}
              maxLength={4}
              inputMode="numeric"
              help={fieldHelp(errors[index].year, null)}
              onChange={(value) => updateTestimonial(index, 'year', value)}
            />
            {TEXT_FIELDS.map(({ key, label, Control }) => (
              <Control
                key={key}
                label={label}
                value={testimonial[key] ?? ''}
                maxLength={TESTIMONIAL_MAX_LENGTHS[key]}
                help={fieldHelp(errors[index][key], remainingCharacters(key, testimonial[key]))}
                onChange={(value) => updateTestimonial(index, key, value)}
              />
            ))}
            <PhotoPicker
              imageId={testimonial.imageId}
              error={errors[index].imageId}
              onChange={(imageId) => updateTestimonial(index, 'imageId', imageId)}
            />
          </CardBody>
        </Card>
      ))}
      <Button variant="primary" onClick={addTestimonial}>
        {__('Ajouter un témoignage', 'amnesty')}
      </Button>
    </div>
  );
};

export default EditComponent;
