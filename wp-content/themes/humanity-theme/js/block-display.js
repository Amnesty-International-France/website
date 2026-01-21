wp.domReady(() => {
  const hidden_blocks = [
    'core/group',
    // Texte
    'core/quote',
    'core/accordion',
    'jetpack/ai-assistant',
    'jetpack/ai-chat',
    'jetpack/blogging-prompt',
    // Centre d'administration
    'amnesty-core/pop-in',
    'amnesty-core/sidebar',
    'amnesty-core/tweet-block',
    'amnesty-core/block-menu',
    // Média
    'core/cover',
    'core/file',
    'core/audio',
    'videopress/video',
    'core/gallery',
    // Design
    'core/buttons',
    'core/separator',
    // Widgets
    'core/latest-posts',
    'jetpack/rating-star',
    'jetpack/repeat-visitor',
    'jetpack/related-posts',
    'jetpack/top-posts',
    'jetpack/slideshow',
    'jetpack/tiled-gallery',
    // Thème
    'core/navigation',
    'core/post-excerpt',
    // Contenus embarqués
    'jetpack/eventbrite',
    'jetpack/goodreads',
    // Monétiser
    'jetpack/opentable',
    'jetpack/donations',
    'jetpack/paywall',
    'jetpack/premium-content',
    'jetpack/payment-buttons',
    'jetpack/wordads',
    'jetpack/tock',
    'jetpack/payments-intro',
    'jetpack/paypal-payment-buttons',
    // Développer
    'jetpack/business-hours',
    'jetpack/calendly',
    'jetpack/mailchimp',
    'jetpack/subscriber-login',
    'jetpack/subscriptions',
    'jetpack/like',
    'jetpack/contact-info',
    'jetpack/send-a-message',
    'jetpack/blog-stats',
    'jetpack/blogroll',
    'jetpack/nextdoor',
  ];

  const hidden_blocks_variation = [
    ['core/group', 'group-row'],
    ['core/group', 'group-stack'],
    ['core/group', 'group-grid'],
    ['core/embed', 'facebook'],
    ['core/embed', 'smartframe'],
    ['core/embed', 'descript'],
  ];

  hidden_blocks.forEach((name) => {
    if (wp.blocks.getBlockType(name)) {
      wp.blocks.unregisterBlockType(name);
    }
  });

  hidden_blocks_variation.forEach((variation) => {
    const name = variation[0];
    const type = variation[1];
    if (wp.blocks.getBlockType(name)) {
      wp.blocks.unregisterBlockVariation(name, type);
    }
  });

  const interactive_map_block = wp.blocks.getBlockType('create-block/interactive-map');
  if (interactive_map_block) {
    wp.blocks.unregisterBlockType(interactive_map_block.name);
    wp.blocks.registerBlockType(interactive_map_block.name, {
      ...interactive_map_block,
      title: 'Carte structures locales',
    });
  }
});
