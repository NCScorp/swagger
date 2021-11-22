const path = require('path');
const webpack = require('webpack');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const resolveTsconfigPathsToAlias = require('./resolve-tsconfig-path-to-alias');
const devMode = process.env.NODE_ENV;

module.exports = {
    devtool: 'source-map',
    mode: devMode,
    entry: {
        vendor:
            [
                //Recursos do AngularJS
                './node_modules/@uirouter/angularjs/release/angular-ui-router.min.js',
                './node_modules/angular-messages/angular-messages.min.js',
                './node_modules/angular-animate/angular-animate.min.js',
                './node_modules/angular-ui-bootstrap/dist/ui-bootstrap-tpls.js',
                './node_modules/angular-sanitize/angular-sanitize.min.js',
                './node_modules/angular-moment/angular-moment.min.js',
                './node_modules/angularjs-toaster/toaster.min.js',
                './node_modules/angular-file-input/dist/angular-file-input.min.js',
                './node_modules/angular-ui-mask/dist/mask.min.js',
                './node_modules/angular-filter/dist/angular-filter.min.js',
                './node_modules/angular-locale-pt-br/angular-locale_pt-br.js',
                './node_modules/angular-cookies/angular-cookies.min.js',
                './node_modules/bootstrap/dist/js/bootstrap.min.js',

                //Recursos dependentes do AngularJS
                './node_modules/ui-select/dist/select.min.js',
                './node_modules/moment-timezone/builds/moment-timezone-with-data.min.js',
                './node_modules/moment/min/moment.min.js',
                './node_modules/moment/locale/pt-br.js',
                './node_modules/multiple-date-picker/dist/multipleDatePicker.min.js',

                //Dropzone
                './src/assets/lib/dropzone.js',

                //TreeGrid
                './node_modules/angular-bootstrap-grid-tree/src/tree-grid-directive.js',

                //Symfony
                './src/assets/lib/router.js',

                //Nasajon-ui
                './node_modules/nasajon-ui/lib/nasajon-ui.ts',

                //Compatibilidade Nasajon-ui
                './node_modules/nasajon-ui/nasajon-ui-old/utils/nsj/globals/globals.min.js',
                './node_modules/nasajon-ui/nasajon-ui-old/utils/is_state_filter.js',
                './node_modules/nasajon-ui/nasajon-ui-old/utils/debounce.js',
                './node_modules/nasajon-ui/nasajon-ui-old/forms/select/mdauiselect.js',
                './node_modules/nasajon-ui/nasajon-ui-old/tables/objectlist.js',
                './node_modules/nasajon-ui/nasajon-ui-old/forms/js/convert_to_number.js',
                './node_modules/nasajon-ui/nasajon-ui-old/forms/js/date_input.js',
                './node_modules/nasajon-ui/nasajon-ui-old/forms/js/filters.js',
                './node_modules/nasajon-ui/nasajon-ui-old/forms/js/ui_mask_filter.js',
                './node_modules/nasajon-ui/nasajon-ui-old/forms/js/cpf_cnpj.js',

                './src/main.ts'
            ]


        , styles: [
            './src/assets/sass/style.scss',
            './node_modules/@fortawesome/fontawesome-free/css/all.css',
            './node_modules/multiple-date-picker/dist/multipleDatePicker.css',
        ],
    },
    output: {
        path: path.resolve(__dirname, 'home'),
        publicPath: devMode == 'production' ? '/monitor-financeiro-gcf/' : '/'
    },
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                use: [{
                    loader: 'awesome-typescript-loader',
                    options: {
                        transpileOnly: true,
                    },
                },],
            },
            {
                test: /\.html$/,
                loader: 'html-loader'
            },
            {
                test: /\.(png|jpe?g|gif|svg)$/i,
                use: [{
                    loader: 'file-loader'
                }]
            },
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
                test: /\.(svg|woff|woff2|eot|ttf)$/,
                use: 'file-loader?outputPath=fonts/',
            },
        ]
    },
    resolve: {
        alias: resolveTsconfigPathsToAlias(),
        modules: [path.resolve(__dirname, 'node_modules')],
        extensions: ['.ts', '.js']
    },
    plugins: [
        new CleanWebpackPlugin(),
        new HtmlWebpackPlugin({
            template: path.resolve(__dirname, 'src/app/index.html')
        }),
        new webpack.ProvidePlugin({
            'moment': 'moment',
            'window.moment': 'moment',
            'window.Dropzone': 'dropzone',
            'window.treeGrid': 'treeGrid',
            'window.jQuery': 'jquery',
            '$': 'jquery',
            'jQuery': 'jquery',
        }),
        new CopyWebpackPlugin([
            {
                from: path.resolve(__dirname, "src/config/*"),
                to: path.resolve(__dirname, "dist")
            }
        ])

    ],
    devServer: {
        contentBase: path.join(__dirname, 'dist'),
        historyApiFallback: true,
        compress: true,
        port: 9000,
        host: '0.0.0.0'
    }
}
