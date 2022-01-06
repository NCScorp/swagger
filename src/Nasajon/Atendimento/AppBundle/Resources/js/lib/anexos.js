angular.module('anexos', ['nsjRouting', 'ngFileUpload', 'bootstrapLightbox'])
        .directive('anexos', ['nsjRouting', function (nsjRouting) {
                return {
                    restrict: 'E',
                    templateUrl: nsjRouting.generate('template_anexos', {}, false),
                    scope: {
                        model: '=model',
                        canEdit: "=",
                        disabled: "="
                    },
                    controller: ['$scope', 'Upload', '$rootScope', 'Lightbox', function ($scope, Upload, $rootScope, Lightbox) {
                            Lightbox.fullScreenMode = true;
                            Lightbox.templateUrl = nsjRouting.generate('template_anexos_preview_modal');
                            
                            if (!$scope.model) {
                                $scope.model = [];
                            }

                            $scope.remove = function (index) {
                                $scope.model.splice(index, 1);
                            };
                            
                            
                            $scope.sendFile = function (index) {
                                $scope.model[index].error = false;
                                
                                $rootScope.$broadcast("uploadAnexos", true);
                                
                                Upload.upload({
                                    url: nsjRouting.generate('_uploader_upload_anexos'),
                                    data: {file: $scope.model[index].file}
                                }).then(function (resp) {
                                    $scope.model[index].going = false;
                                    $scope.model[index].url = resp.data['url'];
                                    $scope.model[index].file = null;
                                    $scope.model[index].documentoged = resp.data['documentoged'];
                                    $rootScope.$broadcast("uploadAnexos", false);

                                }, function (resp) {
                                    $scope.model[index].error = true;

                                }, function (evt) {
                                    var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                                    $scope.model[index].perc = progressPercentage;

                                });
                            };

                            $scope.upload = function (file) {
                                if (file) {
                                    var i = $scope.model.length;
                                    var mimetype = file.type.split("/");
                                    var type = (mimetype[0] === 'image') ? mimetype[0] : mimetype[1];

                                    $scope.model.push({
                                        going: true,
                                        perc: 0,
                                        file: file,
                                        nome: file.name,
                                        url: '',
                                        tipo: type,
                                        documentoged: null,
                                        error: false
                                    });
                                    
                                    $scope.sendFile(i);
                                }
                            };

                            $scope.retry = function (index) {
                                $scope.sendFile(index);
                            };
                            $scope.uploadFiles = function (files) {
                                if (files && files.length) {
                                    for (var i = 0; i < files.length; i++) {
                                        $scope.upload(files[i]);
                                    }
                                }
                            };
                            
                            $scope.open = function(i,item) {
                              if($scope.model[i].tipo != 'image'){
                                window.open($scope.model[i].url, '_blank');
                              }else{
                                var imgs = $scope.model.filter(function(img){
                                  if(img.tipo=='image'){
                                    return img;
                                  }
                                });
                                Lightbox.openModal(imgs, imgs.indexOf(item));
                              }
                            };

                        }]
                };
            }]);
        