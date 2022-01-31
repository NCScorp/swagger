import { NsjIndiceController } from './nsjindice.controller';

export class NsjIndiceComponent implements angular.IComponentOptions {
    static selector = 'nsjIndice';
    static bindings = {
        config: '='
    };
    static controller = NsjIndiceController;
    static template = require('./nsjindice.html');
}
