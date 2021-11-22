
import * as angular from 'angular';

import { NSDropzoneComponent } from './components/nsdropzone.component';
import { NSDropzoneDirective } from './components/nsdropzone.directive';

export const nsDropzone =
  angular.module('nsDropzone', [ ])   
         .directive('nsdropzone', NSDropzoneDirective.Factory())     
         .component(NSDropzoneComponent.selector, NSDropzoneComponent)
         .name;



