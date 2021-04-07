const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .enableSingleRuntimeChunk()
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addStyleEntry('tailwind', './assets/css/tailwind.css')
    // enable post css loader
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            // directory where the postcss.config.js file is stored
            config: './postcss.config.js'
        };
    });
module.exports = Encore.getWebpackConfig()