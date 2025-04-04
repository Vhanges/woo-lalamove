const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const webpack = require('webpack'); 
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin'); 
const TerserPlugin = require('terser-webpack-plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin; 

module.exports = {
    entry: './assets/js/index.js',
    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, 'assets/js/dist'),
        publicPath: '/wp-content/plugins/woo-lalamove/assets/js/dist/',
        clean: true,
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
                test: /\.(png|jpe?g|gif|svg)$/, 
                type: 'asset/resource',
                generator: {
                    filename: 'images/[name][ext]' 
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
                test: /\.scss$/,
                use: [
                    'vue-style-loader',
                    'css-loader',
                    'sass-loader',
                ]
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
            '@': path.resolve(__dirname, 'assets'), 
            '@component': path.resolve(__dirname, 'assets/js/vue/components'), 
            '@views': path.resolve(__dirname, 'assets/js/vue/views'),
            '@eventBus': path.resolve(__dirname, 'assets/js/utils'), 
            '@images': path.resolve(__dirname, 'assets/images')
        },
        extensions: ['.js', '.vue', '.json']
    },
    plugins: [
        new VueLoaderPlugin(),
        ,
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: JSON.stringify(false), // Disabled Options API
            __VUE_PROD_DEVTOOLS__: JSON.stringify(true),
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(false)
        }),
        new BundleAnalyzerPlugin()
    ],
    mode: 'production',
    devtool: 'source-map',
    optimization: {
        minimize: true,
        minimizer: [
            new CssMinimizerPlugin(),
            new TerserPlugin()
        ]
    },
    externals: {
        jquery: 'jQuery',
    }
};
