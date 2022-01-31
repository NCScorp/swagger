
import * as angular from 'angular';

import { NsjIndiceComponent } from './components/nsjindice.component';

export const nsjIndice =
    angular.module('nsjIndice', [
        'treeGrid'
    ])     
    .component(NsjIndiceComponent.selector, NsjIndiceComponent)
    .name;



