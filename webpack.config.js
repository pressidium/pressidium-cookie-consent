const path = require('path');
const defaults = require('@wordpress/scripts/config/webpack.config');

module.exports = {
  ...defaults,
  entry: {
    'bundle.admin': path.resolve(process.cwd(), 'src', 'admin', 'index.js'),
    'bundle.client': path.resolve(process.cwd(), 'src', 'client', 'index.js'),
    'bundle.feedback': path.resolve(process.cwd(), 'src', 'feedback', 'index.js'),
    'block-scripts': path.resolve(process.cwd(), 'src', 'client', 'block-scripts.js'),
    'consent-mode': path.resolve(process.cwd(), 'src', 'client', 'consent-mode.js'),
  },
  output: {
    filename: '[name].js',
    path: path.resolve(process.cwd(), 'public'),
  },
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM',
  },
  resolve: {
    extensions: [...(defaults.resolve ? defaults.resolve.extensions || ['.js', 'jsx'] : [])],
  },
};
