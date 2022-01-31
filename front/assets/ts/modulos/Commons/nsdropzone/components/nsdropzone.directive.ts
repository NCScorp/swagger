import angular = require('angular');

const  Dropzone =  require('dropzone');

export class NSDropzoneDirective implements angular.IDirective {

    public link;

    restrict = 'A';
    scope = {
        options: '=',
        model: '=',
        addFile: '&',
        removeFile: '&',
        successFile: '&',
        errorFile: '&',
        processQueuee: '&',
        envioDesabilitado: '='
    };

    public static Factory() {
        var directive = () => {
            return new NSDropzoneDirective();
        };

        return directive;
    }

    constructor() {

        NSDropzoneDirective.prototype.link = (scope: any, element: any, attr: any) => {

            let options = scope.options;

            let currentFile = null;

            let eventHandlers = {
                'addedfile': function (file: any) {

                    currentFile = file;

                    scope.addFile({ file: file });
                },

                'removedfile': function (file: any) {
                    scope.removeFile({ file: file });
                },

                'success': function (file: any, response: any) {

                    response.file = file;

                    scope.successFile({ response: response });
                },

                'error': function (file: any, response: any) {
                    if (typeof response == 'object') {
                        response.file = file;
                    }

                    scope.errorFile({ 
                        response: response,
                        file: file
                    });
                }
            };

            let dropzone = new Dropzone(element[0], options);

            if (scope.envioDesabilitado) {
                dropzone.disable();
            }

            angular.forEach(eventHandlers, function (handler: any, event: any) {
                dropzone.on(event, handler);
            });

            scope.$on('processQueue', () => {
                dropzone.processQueue();
            });

            scope.$on('removeFile', () => {
                dropzone.removeFile(currentFile);
            });

            scope.$on('forceDropzoneClick', () => {
                dropzone.hiddenFileInput.click();
            });

            if (scope.model) {

                scope.model.forEach(item => {

                    let mockFile = {
                        name: item.name,
                        size: item.size,
                        type: item.type,
                        accepted: true,
                        created: item.created,
                        uuid: item.id
                    };

                    dropzone.emit('addedfile', mockFile);
                    dropzone.emit('thumbnail', mockFile, item.url);
                    dropzone.emit('complete', mockFile);

                });
            }


            scope.processDropzone = function () {
                dropzone.processQueue();
            };

            scope.resetDropzone = function () {
                dropzone.removeAllFiles();
            };
        };
    }

}
