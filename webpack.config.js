// webpack.config.js
const path = require('path');

module.exports = {
  entry: './public/assets/js/editor.js',
  output: {
    filename: 'editor.bundle.js',
    path: path.resolve(__dirname, 'public/assets/js'),
  },
  mode: 'production',
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
          },
        },
      },
    ],
  },
};