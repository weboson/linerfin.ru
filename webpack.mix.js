const mix = require('laravel-mix');
const path = require('path');


mix // >>
    .webpackConfig({
        resolve: {
            // aliases
            alias: {
                '@': path.resolve(__dirname, 'resources/js'),
                '@sass': path.resolve(__dirname, 'resources/sass'),

                // for js files mark ~ to disable PHPStorm path warnings
                '~@vue': path.resolve(__dirname, 'resources/js/Vue'),
                '~@vueComponents': path.resolve(__dirname, 'resources/js/Vue/Components'),
                '~@vueMixins': path.resolve(__dirname, 'resources/js/Vue/mixins'),
            }
        }
    })


    // [main module]
    .sass('resources/sass/app.scss', 'public/css', {
        additionalData: `@import "@sass/_variables.sass";`
    })
    .js('resources/js/app.js', 'public/js')

    // [authorize module]
    // .js('resources/js/app-auth.js', 'public/js')
    // .sass('resources/sass/auth/page.scss', 'public/css/auth.css')


    // Enable vue-loader with global sass variables
    .vue({ globalStyles: "resources/sass/_variables.sass" })
    // .sourceMaps()

// <<








// [test experiment]
// const { VueLoaderPlugin } = require('vue-loader');
/*module.exports = {
    entry: './resources/js/app.js',
    output: {
        path: path.resolve(__dirname, 'public/js'),
        filename: 'app.js'
    },

    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            }
        ]
    },

    resolve: {
        // aliases
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@sass': path.resolve(__dirname, 'resources/sass'),
        }
    },
    plugins: [
        new VueLoaderPlugin()
    ]
}*/
