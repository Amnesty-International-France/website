const autoprefixer = require('autoprefixer');
const pxtorem = require('postcss-pxtorem');
const reporter = require('postcss-reporter');

module.exports = {
  plugins: [
    autoprefixer(),
    pxtorem({
      prop_white_list: ['font', 'font-size', 'line-height', 'letter-spacing'],
      exclude: /node_modules/i,
    }),
    reporter({ clearMessages: true }),
  ],
};
