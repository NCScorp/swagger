var webpack = require('webpack');
var HtmlWebpackPlugin = require('html-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
var path = require('path');
var BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const LoaderOptionsPlugin = require('webpack/lib/LoaderOptionsPlugin');
const TerserPlugin = require('terser-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const glob = require('glob');
const devMode = process.env.NODE_ENV !== 'production';
const resolveTsconfigPathsToAlias = require('./resolve-tsconfig-path-to-alias');

// const devMode = true;
const publicPath = '/';

const ROOT_DIR = path.resolve(__dirname, './');

function getPlugins() {
    var plugins = [
        new CleanWebpackPlugin(),
        new webpack.DefinePlugin({
            'process.env': {
                'nodeEnv': process.env.NODE_ENV
            }
        }),
        new webpack.ProvidePlugin({
            'window.moment': 'moment',
            'window.Dropzone': 'dropzone',
        }),
     
        new HtmlWebpackPlugin({
            // template: path.resolve(__dirname, './assets/ts/modulos/index.html'),
            template: path.resolve(ROOT_DIR, 'assets/ts/modulos/index.html')
        }),
        new BrowserSyncPlugin(
            {
                host: 'localhost',
                port: '3000',
                proxy: 'http://localhost:80/',
                open: false
            },
            {
                reload: true
            }
        ),
        // new webpack.SourceMapDevToolPlugin({
        //     filename: "sourcemap/[file].map"
        // }),
        new LoaderOptionsPlugin({
            debug: true,
            options: {
                tslint: {
                    configuration: require('./tslint.json'),
                    typeCheck: true
                }
            }
        }),
        new CopyWebpackPlugin([
            // {
            //     from: path.resolve(__dirname, "assets/ts/modulos/*"),
            //     to: path.resolve(__dirname, "dist")
            // },
            {
                from: path.resolve(__dirname, "assets/img/**/*"),
                to: path.resolve(__dirname, "dist")
            },
            {
                from: path.resolve(__dirname, "assets/css/**/*"),
                to: path.resolve(__dirname, "dist")
            },
            {
                from: path.resolve(__dirname, "config/*"),
                to: path.resolve(__dirname, "dist")
            }
        ]),
    ];

    return plugins;
}


function getEnvironment() {
    return process.env.NODE_ENV;
}

module.exports = {
    
    mode: getEnvironment(),
    devtool:'source-map',
   
    watchOptions:{
        aggregateTimeout: 300,
        poll: true,
        ignored: [ 
            /node_modules/,
            /src/ 
        ]
    },
    context: path.resolve(__dirname, '.'),
    
    optimization: {
        removeAvailableModules: false,
        removeEmptyChunks: false,
        splitChunks: false,
        runtimeChunk: 'single',
        nodeEnv: process.env.NODE_ENV,
        minimizer: devMode ? [] : [
            new TerserPlugin({})
        ]
    },
    resolve: {
        alias: resolveTsconfigPathsToAlias(),
        extensions: ['.ts', '.js'],
        cacheWithContext: false,
        symlinks: false
    },
    entry: {
        vendor: [
            path.resolve(ROOT_DIR, 'assets/ts/main.ts'),

        ],
        css: [
            path.resolve(ROOT_DIR, 'assets/sass/style.scss'),
            path.resolve(ROOT_DIR, 'node_modules/angular-tree-dnd/dist/ng-tree-dnd.css'),
            path.resolve(ROOT_DIR, 'node_modules/@fortawesome/fontawesome-free/css/all.css'),
            path.resolve(ROOT_DIR, 'node_modules/multiple-date-picker/dist/multipleDatePicker.css'),
            path.resolve(ROOT_DIR, 'node_modules/angularjs-toaster/toaster.min.css'),
            path.resolve(ROOT_DIR, 'node_modules/ng-tags-input/build/ng-tags-input.min.css')
        ],
    },
    output: {
        pathinfo: false,
        filename: getEnvironment() === 'production' ? '[name]-[hash:6].min.js' : '[name].js',
        // path: path.resolve(__dirname, './web/assets'),
        path: path.resolve(__dirname, 'dist'),
        publicPath: publicPath,
    },
    devServer: {
        contentBase: path.join(__dirname, 'dist'),
        historyApiFallback: true,
        compress: true,
        port: 9000,
        host: '0.0.0.0'
    },
    plugins: getPlugins(),
    module: {

        rules: [
            {
                include: [
                    path.resolve(__dirname, 'assets/ts'),
                    // path.resolve(__dirname, 'src/Nasajon/MDABundle/Resources/js'),
                    path.resolve(__dirname, 'assets'),
                    path.resolve(__dirname, 'node_modules/nasajon-ui/nasajon-ui-old'),
                ],
                test: /\.ts$/,
                use: [
                    {
                        loader: 'ts-loader',
                        options: {
                            transpileOnly: true,
                            experimentalWatchApi: true,
                            happyPackMode: true
                        },
                    },
                ],
            },
            // {
            //     test: /\.html?$/,
            //     include: /node_modules/,
            //     use: [
            //         {
            //             loader: 'html-loader'
            //         }
            //     ]
            // },
            {
                test: /\.html$/,
                loader: 'html-loader'
            },
            // {
            //     test: /\.(sa|sc|c)ss$/,
            //     use: [
            //         devMode ? 'style-loader' : MiniCssExtractPlugin.loader, {
            //             loader: 'css-loader',
            //             options: {
            //                 // sourceMap: true
            //             }
            //         },
            //         {
            //             loader: 'resolve-url-loader',
            //         },
            //         {
            //             loader: 'sass-loader',
            //             options: {
            //                 // outputStyle: 'compressed',
            //                 sourceMap: true
            //             }
            //         }
            //     ]
            // },
            {
                test: /\.(sa|sc|c)ss$/,
                use: [
                    'style-loader', {
                        loader: 'css-loader',
                        options: {
                            sourceMap: true
                        }
                    },
                    {
                        loader: "resolve-url-loader",
                        options: {
                            keepQuery: true,
                            sourceMap: true,
                            sourceMapContents: false
                        }
                    },
                    {
                        loader: 'sass-loader',
                        options: {
                            sourceMap: true
                        }
                    }
                ]
            },
            {
                test: /\.(jpg|png|gif)$/,
                use: 'file-loader?outputPath=img/',
            },

            {
                test: /\.(svg|woff|woff2|eot|ttf)$/,
                use: 'file-loader?outputPath=fonts/',
            },

        ]
    }
};
