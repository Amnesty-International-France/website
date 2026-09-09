const { addFilter } = wp.hooks;

addFilter('blocks.registerBlockType', 'amnesty-core/image-inserter', (settings, name) => {
  if (name !== 'core/image') {
    return settings;
  }

  return {
    ...settings,
    supports: {
      ...settings.supports,
      inserter: false,
    },
  };
});
