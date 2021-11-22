import angular = require('angular');

export class NSDropzoneDirective implements angular.IDirective {

    public link;

    restrict = 'A';
    scope = {
        options: '=',
        model: '=',
        addFile: '&',
        removeFile: '&',
        successFile: '&',
        errorFile: '&'
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

            let eventHandlers = {
                'addedfile': function (file) {

                    scope.addFile({ file: file });
                    // scope.file = file;
                    // if (this.files[1] != null) {
                    //     this.removeFile(this.files[0]);
                    // }
                    // scope.$apply(function () {
                    //     scope.fileAdded = true;
                    // });
                },

                'removedfile': function (file) {
                    scope.removeFile({ file: file });
                },

                'success': function (file, response) {

                    response.file = file;

                    scope.successFile({ response: response });
                },

                'error': function (file, response) {

                    response.file = file;

                    scope.errorFile({ response: response });
                }

            };

            let dropzone = new (<any>window).Dropzone(element[0], options);

            angular.forEach(eventHandlers, function (handler, event) {
                dropzone.on(event, handler);
            });

            if (scope.model) {

                scope.model.forEach(item => {

                    let mockFile = {
                        name: item.name,
                        size: item.size,
                        type: item.type,
                        accepted: true,
                        created: item.created,
                        uuid: item.id,
                        url: item.url
                    };

                    dropzone.emit("addedfile", mockFile);
                    dropzone.emit("thumbnail", mockFile, item.url);
                    dropzone.emit("complete", mockFile);

                });
            }


            scope.processDropzone = function () {
                dropzone.processQueue();
            };

            scope.resetDropzone = function () {
                dropzone.removeAllFiles();
            }

            scope.$on('processQueue', () => {
                dropzone.processQueue();
            });

        };

    }




}