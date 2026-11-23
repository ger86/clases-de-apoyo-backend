import Encore from '@symfony/webpack-encore';

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .addEntry('home', [
      './assets/views/home/home.css'
    ])
    .addEntry('app', './assets/app.js')
    .splitEntryChunks()

    .enableSingleRuntimeChunk()

    // configure Babel
    .configureBabel((config) => {
      config.plugins.push('@babel/plugin-transform-class-properties');
      // Babel 8 removed useBuiltIns/corejs from preset-env; this plugin adds the same "usage" polyfills.
      config.plugins.push(['babel-plugin-polyfill-corejs3', { method: 'usage-global', version: '3.51' }]);
    })

    .enableSourceMaps(!Encore.isProduction())

    // create hashed filenames (e.g. app.abc123.css)
    .enableVersioning()
    .splitEntryChunks()
    .enablePostCssLoader()
;

export default Encore.getWebpackConfig();