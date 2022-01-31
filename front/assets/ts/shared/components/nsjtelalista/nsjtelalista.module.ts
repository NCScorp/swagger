
import * as angular from 'angular';

import { NsjTelaListaComponent } from '../nsjtelalista/components/nsjtelalista.component';

export const nsjTelaLista =
    angular.module('nsjTelaLista', [])
    .component(NsjTelaListaComponent.selector, NsjTelaListaComponent)
    .name;

