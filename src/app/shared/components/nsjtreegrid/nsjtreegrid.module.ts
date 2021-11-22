
import * as angular from 'angular';

import { NsjTreeGridComponent } from './components/nsjtreegrid.component';

export const nsjTreeGrid =
    angular.module('nsjTreeGrid', [
        'treeGrid'
    ])     
    .component(NsjTreeGridComponent.selector, NsjTreeGridComponent)
    .name;



