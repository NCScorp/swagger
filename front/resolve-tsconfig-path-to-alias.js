const {resolve, join} = require('path');

/**
 * Resolve tsconfig.json paths to Webpack aliases
 * @param  {string} tsconfigPath           - Path to tsconfig
 * @param  {string} webpackConfigBasePath  - Path from tsconfig to Webpack config to create absolute aliases
 * @return {object}                        - Webpack alias config
 */
function resolveTsconfigPathsToAlias({   tsconfigPath = './tsconfig.json',
                                         webpackConfigBasePath = __dirname,
                                     } = {}) {
    const {paths} = require(tsconfigPath).compilerOptions;
    
    const aliases = {};
    Object.keys(paths).forEach((item) => {
        const key = item.replace('/*', '');
        const value = resolve(webpackConfigBasePath, paths[item][0].replace('/*', '').replace('*', ''));

        aliases[key] = value;
    });
    //
    aliases['angular'] = resolve(join(__dirname, 'node_modules', 'angular'));
    aliases['sortablejs'] = resolve(__dirname, 'node_modules/sortablejs/Sortable.min.js');

    return aliases;
}

module.exports = resolveTsconfigPathsToAlias;
