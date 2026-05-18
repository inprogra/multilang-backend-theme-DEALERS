const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const WebpackAssetsManifest = require('webpack-assets-manifest');

const mode = process.env.NODE_ENV || 'development';

const target = process.env.NODE_ENV === "production" ? "browserslist" : "web";

module.exports = {
    // mode defaults to 'production' if not set
    mode: mode,

    plugins: [
        new MiniCssExtractPlugin({
            filename: mode === 'development' ? '[name].css' : '[name].[contenthash].min.css',
        }),
        new WebpackAssetsManifest()
    ],

    entry: {
        app: ['./assets/private/js/app.js', './assets/private/css/app.scss'],
        admin: ['./assets/private/js/admin.js', './assets/private/css/admin.scss'],
        editor: ['./assets/private/js/editor.js', './assets/private/css/editor.scss'],
        cache: ['./assets/private/js/cache.js', './assets/private/css/cache.scss']
    },

    output: {
        publicPath: '',
        filename: mode === 'development' ? '[name].js' : '[name].[contenthash].min.js',
        path: path.resolve(__dirname, 'assets', 'public')
    },

    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    // without additional settings, this will reference .babelrc
                    loader: 'babel-loader',
                },
            },
            {
                test: /\.scss$/,
                use: [
                    // fallback to style-loader in development
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    'postcss-loader',
                    'resolve-url-loader',
                    'sass-loader',
                ],
            },
            {
                test: /\.(png|jpe?g|gif|svg|woff(2)?|ttf|eot)(\?v=\d+\.\d+\.\d+)?$/,
                use: [{
                    loader: 'file-loader',
                    options: {
                        name: mode === 'development' ? '[name].[ext]' : '[name].[contenthash].[ext]',
                    }
                }]
            },
        ],
    },

    target: target,
    devtool: 'source-map',
};