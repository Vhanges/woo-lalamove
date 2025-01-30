const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');

module.exports = {
    entry: './assets/js/index.js',
    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, 'assets/js/dist'),
        publicPath: '/wp-content/plugins/woo-lalamove/assets/js/dist/', // Add this for WordPress
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: 'vue-loader',
                options: {                           // Add compiler options
                    compilerOptions: {
                        isCustomElement: tag => tag.startsWith('wc-') // If using WooCommerce components
                    }
                }
            },
            {
                test: /\.js$/,
                exclude: /node_modules\/(?!vue)/,    // Important for Vue 3 SSR
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            ['@babel/preset-env', { 
                                targets: "> 0.25%, not dead", 
                                modules: false       // Enable tree-shaking
                            }]
                        ],
                        plugins: ['@babel/plugin-transform-runtime']
                    }
                }
            },
            {
                test: /\.css$/,
                use: [
                    'vue-style-loader',              // Better for Vue SSR
                    'css-loader',
                    {
                        loader: 'postcss-loader',    // Add PostCSS
                        options: {
                            postcssOptions: {
                                plugins: [
                                    require('autoprefixer')
                                ]
                            }
                        }
                    }
                ]
            }
        ]
    },
    resolve: {
        alias: {
            vue$: 'vue/dist/vue.esm-bundler.js',
            '@': path.resolve(__dirname, 'assets/js') // Add path alias
        },
        extensions: ['.js', '.vue', '.json']
    },
    plugins: [
        new VueLoaderPlugin()
    ],
    mode: 'production',
    devtool: 'source-map',
    externals: {                                     // For WordPress integration
        jquery: 'jQuery',
    }
};