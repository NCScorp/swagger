import { NsjTreeGridController } from './nsjtreegrid.controller';

export class NsjTreeGridComponent implements angular.IComponentOptions {
    static selector = 'nsjTreeGrid';
    static bindings = {
        config: '='
    };
    static controller = NsjTreeGridController;
    static template = require('!!html-loader!./nsjtreegrid.html');
}
