import { NSDropzoneController } from './nsdropzone.controller';

export class NSDropzoneComponent implements angular.IComponentOptions {

  static selector = 'nsDropzone';
  static bindings = {
         options: '=',
         model:'=',
         apresentarBtnAddAnexo:'<',
         addFile: '&',
         removeFile: '&',
         successFile: '&',
         errorFile: '&'
   };
  static controller = NSDropzoneController;
  static template = require('!!html-loader!./nsdropzone.html');

}
