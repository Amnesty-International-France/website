wp.domReady(() => {
  const hidden_blocks = [
    // Texte
    'core/quote',
    'jetpack/ai-assistant',
    'jetpack/ai-chat',
    'jetpack/blogging-prompt',
    // Centre d'administration
    'amnesty-core/pop-in',
    'amnesty-core/sidebar',
    'amnesty-core/tweet-block',
    'amnesty-core/articles-homepage',
    'amnesty-core/block-menu',
    'amnesty/rubric-heading',
    // Média
    'core/cover',
    'core/file',
    'core/audio',
    'videopress/video',
    // Design
    'core/buttons',
    'core/spacer',
    'core/separator',
    // Widgets
    'core/latest-posts',
    'jetpack/rating-star',
    'jetpack/repeat-visitor',
    'jetpack/related-posts',
    'jetpack/top-posts',
    // Thème
    'core/navigation',
    'core/post-excerpt',
    // Contenus embarqués
    'jetpack/eventbrite',
    'jetpack/goodreads',
    // Monétiser
    'jetpack/opentable',
    'jetpack/donations',
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
