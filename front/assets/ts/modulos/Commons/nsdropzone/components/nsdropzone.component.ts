import { NSDropzoneController } from './nsdropzone.controller';

export class NSDropzoneComponent implements angular.IComponentOptions {

  static selector = 'nsDropzone';
  static bindings = {
         options: '=',
         model: '=',
         addFile: '&',
         removeFile: '&',
         successFile: '&',
         errorFile: '&',
         processQueuee: '&',
         envioDesabilitado: '='
   };
  static controller = NSDropzoneController;
  static template = require('./nsdropzone.html');

}
