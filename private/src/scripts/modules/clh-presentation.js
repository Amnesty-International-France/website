const Presentation = () => {
  document.querySelectorAll('.clh-editable .clh-pictos .wp-block-image img').forEach((image) => {
    const figure = image.closest('.wp-block-image');
    const hideMissingImage = () => {
      figure.hidden = true;
    };

    image.addEventListener('error', hideMissingImage, { once: true });
    // A cached failure can occur before the application initializes.
    if (image.complete && image.naturalWidth === 0) hideMissingImage();
  });
};

export default Presentation;
