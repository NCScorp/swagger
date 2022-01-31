import { NsjTelaListaController } from './nsjtelalista.controller';

export class NsjTelaListaComponent implements angular.IComponentOptions {
    static selector = 'nsjTelaLista';
    static bindings = {
        config: '=',
        entities: '=',
        service: '='
    };
    static controller = NsjTelaListaController;
    static template = require('!!html-loader!./nsjtelalista.html');
    static transclude = {
        'filtros': '?nsjTelaListaFiltros'
    };
}
