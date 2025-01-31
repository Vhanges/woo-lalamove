const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const webpack = require('webpack'); // Add this line

module.exports = {
    entry: './assets/js/index.js',
    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, 'assets/js/dist'),
        publicPath: '/wp-content/plugins/woo-lalamove/assets/js/dist/',
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: 'vue-loader',
                options: {
                    compilerOptions: {
                        isCustomElement: tag => tag.startsWith('wc-')
                    }
                }
            },
            {
                test: /\.(png|jpe?g|gif|svg)$/,  // 🔥 Add support for images
                type: 'asset/resource',
                generator: {
                    filename: 'images/[name][ext]' // Saves images in dist/images/
                }
            },
            {
                test: /\.js$/,
                exclude: /node_modules\/(?!vue)/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            ['@babel/preset-env', { targets: "> 0.25%, not dead", modules: false }]
                        ],
                        plugins: ['@babel/plugin-transform-runtime']
                    }
                }
            },
            {
                test: /\.css$/,
                use: [
                    'vue-style-loader',
                    'css-loader',
                    {
                        loader: 'postcss-loader',
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
            '@': path.resolve(__dirname, 'assets/js'), 
            '@component': path.resolve(__dirname, 'assets/js/vue/components'), 
            '@views': path.resolve(__dirname, 'assets/js/vue/views'), 
            '@images': path.resolve(__dirname, 'assets/images')
        },
        extensions: ['.js', '.vue', '.json']
    },
    plugins: [
        new VueLoaderPlugin(),
        ,
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: JSON.stringify(false), // Disable Options API
            __VUE_PROD_DEVTOOLS__: JSON.stringify(true),
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(false)
        })
    ],
    mode: 'production',
    devtool: 'source-map',
    externals: {
        jquery: 'jQuery',
    }
};
