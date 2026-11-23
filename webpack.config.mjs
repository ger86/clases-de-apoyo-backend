var Encore = require('@symfony/webpack-encore');

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
    .enableBuildNotifications()

    // configure Babel
    .configureBabel((config) => {
      config.plugins.push('@babel/plugin-transform-class-properties');
    })

    // enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })

    .enableSourceMaps(!Encore.isProduction())

    // create hashed filenames (e.g. app.abc123.css)
    .enableVersioning()
    .splitEntryChunks()
    .enablePostCssLoader()
;

const localWebpackConfig = Encore.getWebpackConfig();

module.exports = localWebpackConfig;