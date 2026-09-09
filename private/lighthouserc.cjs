module.exports = {
  ci: {
    collect: {
      url: ['http://localhost:8898/', 'http://localhost:8898/newsletter/', 'http://localhost:8898/mot-de-passe-oublie/'],
      numberOfRuns: 3,
      settings: {
        chromeFlags: '--no-sandbox',
      },
    },
    assert: {
      assertions: {
        'categories:performance': ['warn', { minScore: 0.5 }],
        'categories:accessibility': ['warn', { minScore: 0.8 }],
        'categories:seo': ['warn', { minScore: 0.8 }],
      },
    },
    upload: {
      target: 'temporary-public-storage',
    },
  },
};
