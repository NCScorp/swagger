import { optimize, DefinePlugin, ProvidePlugin } from 'webpack';
import { resolve as _resolve, relative, dirname } from 'path';

import HtmlWebpackPlugin from 'html-webpack-plugin';
import ModuleFederationPlugin from 'webpack/lib/container/ModuleFederationPlugin';
import MiniCssExtractPlugin, { loader as _miniCssExtractloader } from 'mini-css-extract-plugin';
import LoaderOptionsPlugin from 'webpack/lib/LoaderOptionsPlugin';
import CssMinimizerPlugin from 'css-minimizer-webpack-plugin';
import CopyWebpackPlugin from 'copy-webpack-plugin';

import { CleanWebpackPlugin } from 'clean-webpack-plugin';

import CompressionPlugin from "compression-webpack-plugin";
import { constants } from "zlib";

import { dependencies as deps } from "../package.json";

const ROOT_DIR = _resolve(__dirname, '../');

export function getOptimization() {
    return {
        splitChunks: {
            chunks: 'all',
        },
        minimizer: [
            new optimize.ModuleConcatenationPlugin(),
            new CompressionPlugin({
                filename: "[path][base].gz",
                test: /\.js$|\.css$|\.html$/,
                minRatio: 0.9,
            }),
            new CompressionPlugin({
                filename: "[path][base].br",
                algorithm: "brotliCompress",
                test: /\.(js|css|html|svg)$/,
                compressionOptions: {
                    params: {
                        [constants.BROTLI_PARAM_QUALITY]: 11,
                    },
                },
                minRatio: 0.9
            }),
            new CssMinimizerPlugin(),
            `...`,
        ]
    }
}

export function getPlugins(env) {

    const clearWebpack = new CleanWebpackPlugin();
    const miniCssExtract = new MiniCssExtractPlugin({
        filename: env.production ? 'css/[name].[chunkhash:6].min.css' : 'css/[name].css',
        // chunkFilename: env.production ? '[id].[chunkhash:6].min.css' : '[id].css',
    });
    const htmlWebpack = new HtmlWebpackPlugin({
        template: _resolve(ROOT_DIR, 'assets/ts/modulos/index.html'),
        hash: true
    });

    const moduleFederation = new ModuleFederationPlugin({
        name: 'crmweb',
        library: { type: 'var', name: 'crmweb' },
        filename: 'remoteEntry.js',
        remotes: {
        },
        exposes: {
            './index': _resolve(ROOT_DIR, 'assets/index'),
            './menu': _resolve(ROOT_DIR, 'config/menu.json')
        },
        shared: {
            'angular': {
                singleton: true
            },
            'single-spa-angularjs': {
                singleton: true
            },
            '@nsj/core': {
                singleton: true,
            },
            moment: deps.moment,

        }
    });

    const webpackDefinePlugin = new DefinePlugin({
        'process.env': {
            'nodeEnv': process.env.NODE_ENV
        }
    });

    const webpackProviderPlugin = new ProvidePlugin({
        'window.moment': 'moment',
        'window.Dropzone': 'dropzone',
    });

    const lintLoader = new LoaderOptionsPlugin({
        debug: true,
        options: {
            tslint: {
                configuration: require(_resolve(ROOT_DIR, 'tslint.json')),
                typeCheck: true
            }
        }
    });

    const copyWebpackPlugin = new CopyWebpackPlugin([
        {
            from: _resolve(ROOT_DIR, "assets/img/**/*"),
            to: _resolve(ROOT_DIR, "dist")
        },
        {
            from: _resolve(ROOT_DIR, "assets/css/**/*"),
            to: _resolve(ROOT_DIR, "dist")
        },
        {
            from: _resolve(ROOT_DIR, "config/*"),
            to: _resolve(ROOT_DIR, "dist")
        }
    ]);

    const commonsPlugins = [clearWebpack, miniCssExtract, htmlWebpack];
    const plugins = [webpackDefinePlugin, webpackProviderPlugin, copyWebpackPlugin, lintLoader]

    if (env.mfe) {
        return [...commonsPlugins, moduleFederation]
    }

    return [...commonsPlugins, ...plugins];
}

export function getExternals(env) {
    if (env.mfe) {
        return [{
            'single-spa-angularjs': 'singleSpaAngularJS',
            'angular': 'angular',
            'uirouter': '@uirouter/angularjs',
            'moment': 'window.moment',
            'dropzone': 'window.Dropzone',
            '@nsj/core': 'NsjCore',
            'nasajon-ui': true
        }]
    }
    return [];
}

export function getEntry(env) {
    if (env.mfe) {
        return _resolve(ROOT_DIR, 'assets/index');
    } else {
        return {
            vendor: [
                _resolve(ROOT_DIR, 'assets/ts/main.ts'),

            ],
            css: [
                _resolve(ROOT_DIR, 'assets/sass/style.scss'),
                _resolve(ROOT_DIR, 'node_modules/angular-tree-dnd/dist/ng-tree-dnd.css'),
                _resolve(ROOT_DIR, 'node_modules/@fortawesome/fontawesome-free/css/all.css'),
                _resolve(ROOT_DIR, 'node_modules/multiple-date-picker/dist/multipleDatePicker.css'),
                _resolve(ROOT_DIR, 'node_modules/angularjs-toaster/toaster.min.css'),
                _resolve(ROOT_DIR, 'node_modules/ng-tags-input/build/ng-tags-input.min.css')
            ],
        }
    }
};

export function getLoaders(env) {
    const tsLoader = {
        test: /\.ts$/,
        include: [
            _resolve(ROOT_DIR, 'assets/ts'),
            _resolve(ROOT_DIR, 'node_modules/nasajon-ui/nasajon-ui-old'),
        ],
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
    };

    const htmlLoader = {
        test: /\.html$/,
        loader: 'html-loader',
        include: [
            _resolve(ROOT_DIR, 'assets'),
            _resolve(ROOT_DIR, 'node_modules/nasajon-ui'),
        ],
        options: {
            minimize: {
                collapseWhitespace: true,
                removeComments: true,
                removeRedundantAttributes: true,
                removeScriptTypeAttributes: true,
                removeStyleLinkTypeAttributes: true,
            }
        }
    };

    const cssLoader = {
        test: /\.(sa|sc|c)ss$/,
        use: _getCssLoaders(env)
    };

    const imageLoader = {
        test: /\.(jpg|png|gif)$/,
        use: 'file-loader?outputPath=img/'
    };

    const fontLoader = {
        test: /\.(svg|woff|woff2|eot|ttf)$/,
        use: 'file-loader?outputPath=fonts/',

    };

    const loaders = {
        rules: [tsLoader, htmlLoader, cssLoader, imageLoader, fontLoader]
    }

    return loaders
}

function _getCssLoaders(env) {
    const loaders = [
        {
            loader: _miniCssExtractloader,
            options: {
                publicPath: (resourcePath, context) => {
                    return relative(dirname(resourcePath), context) + '/';
                },
            }
        },
        {
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
    ]

    if (env.mfe) {
        loaders.push({
            loader: 'postcss-loader',
            options: {
                postcssOptions: {
                    plugins: {
                        "postcss-prefix-selector": {
                            prefix: '#single-spa-crmWeb',
                            transform(prefix, selector, prefixedSelector) {
                                if (selector.match(/^(html|body)/)) {
                                    return selector.replace(/^([^\s]*)/, `$1 ${prefix}`);
                                }
                                return prefixedSelector;
                            },
                        },
                        autoprefixer: {
                            browsers: ['last 4 versions']
                        }
                    }
                }
            }
        })
    }

    loaders.push({
        loader: 'sass-loader',
        options: {
            sourceMap: true,
            implementation: require("sass")
        }
    })

    return loaders
}
