// webpack.config.js
const path = require('path');
const TerserPlugin = require('terser-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');

module.exports = {
  entry: './public/assets/js/editor.js',
  output: {
    filename: '[name].[contenthash].js',
    chunkFilename: '[name].[contenthash].js',
    path: path.resolve(__dirname, 'public/assets/js'),
    clean: true,
  },
  mode: 'production',
  resolve: {
    extensions: ['.js'],
    modules: [path.resolve(__dirname, 'node_modules')],
    alias: {
      // Prevent duplicate modules
      '@ckeditor': path.resolve(__dirname, 'node_modules/@ckeditor'),
    }
  },
  optimization: {
    minimize: true,
    minimizer: [
      new TerserPlugin({
        terserOptions: {
          compress: {
            drop_console: true,
            drop_debugger: true,
            pure_funcs: ['console.log', 'console.info', 'console.debug'],
            passes: 2,
          },
          format: {
            comments: false,
          },
          mangle: {
            safari10: true,
          },
        },
        extractComments: false,
        parallel: true,
      })
    ],
    splitChunks: {
      chunks: 'all',
      minSize: 20000,
      minChunks: 1,
      maxAsyncRequests: 30,
      maxInitialRequests: 30,
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name(module) {
            // Get the package name
            const packageName = module.context.match(/[\\/]node_modules[\\/](.*?)([\\/]|$)/)[1];
            // Remove @ from the package name if it exists
            return `vendor.${packageName.replace('@', '')}`;
          },
          priority: 20,
          reuseExistingChunk: true,
        },
        common: {
          minChunks: 2,
          priority: 10,
          reuseExistingChunk: true,
        },
        // Separate CKEditor into its own chunk
        ckeditor: {
          test: /[\\/]node_modules[\\/]@ckeditor[\\/]/,
          name: 'ckeditor',
          priority: 30,
          reuseExistingChunk: true,
          chunks: 'async',
        }
      },
    },
    // Remove empty chunks
    removeEmptyChunks: true,
    // Merge chunks that contain the same modules
    mergeDuplicateChunks: true,
    runtimeChunk: 'single',
  },
  performance: {
    hints: 'warning',
    maxEntrypointSize: 244000,
    maxAssetSize: 244000,
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              ['@babel/preset-env', {
                targets: {
                  browsers: ['>0.25%', 'not dead', 'not ie 11', 'not op_mini all']
                },
                useBuiltIns: 'usage',
                corejs: 3,
                modules: false,
              }]
            ],
            plugins: [
              ['@babel/plugin-transform-runtime', {
                corejs: 3,
                helpers: true,
                regenerator: true,
                useESModules: true,
              }]
            ],
            cacheDirectory: true,
          },
        },
      },
    ],
  },
  plugins: [
    new CompressionPlugin({
      test: /\.(js|css|html|svg)$/,
      algorithm: 'gzip',
      threshold: 10240,
      minRatio: 0.8,
      deleteOriginalAssets: false,
    }),
  ],
};