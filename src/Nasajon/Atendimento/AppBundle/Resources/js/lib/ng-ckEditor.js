/*
 * autor: Miller Augusto S. Martins
 * e-mail: miller.augusto@gmail.com
 * github: miamarti
 * */
(function (window, document) {
    "use strict";
    (angular.module('ngCkeditor', ['ng']))
    //Corrige bug que ao pressionar o botão voltar do navegador quando o CKEDITOR está em tela cheia, bagunça o DOM.
    .run(['$rootScope',function($rootScope) {
        $rootScope.$on('$locationChangeSuccess', function() {//Back Button Pressed
            for (var editor in CKEDITOR.instances) {
                var editorInFullScren = CKEDITOR.instances[editor].getCommand("maximize").state === 1;
                if (editorInFullScren) CKEDITOR.instances[editor].execCommand('maximize'); //Minimiza o ckeditor
            }
        });
    }])
            
    .directive('ngCkeditor', ['$timeout', 'Upload', 'nsjRouting', '$parse', function ($timeout, Upload, nsjRouting, $parse) {
        CKEDITOR.on('instanceCreated', function (event) {
            var editor = event.editor,
                element = editor.element;
                nsjimage(editor, Upload, nsjRouting);

            if (element.getAttribute('class') == 'simpleEditor') {
                editor.on('configLoaded', function () {
                    editor.config.removePlugins = 'colorbutton,find,flash,font,forms,iframe,image,newpage,removeformat, smiley,specialchar,stylescombo,templates';
                    editor.config.disableNativeSpellChecker = false;
                    editor.removeButtons = 'About', 'Source';
                        removeButtons: 'Source,Save,NewPage,Preview,Print,Templates,Cut,Copy,Paste,PasteText,PasteFromWord,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Strike,Subscript,Superscript,CopyFormatting,Outdent,Indent,Blockquote,CreateDiv,BidiLtr,BidiRtl,Language,Anchor,Image,Flash,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,Styles,Format,BGColor,ShowBlocks,About',
                    editor.config.toolbarGroups = [{
                        name: 'editing',
                        groups: ['basicstyles', 'links']
                    }, {
                        name: 'undo'
                    }, {
                        name: 'clipboard',
                        groups: ['selection', 'clipboard']
                    }];
                });
            };
        });        

        return {
            restrict: 'E',
            scope: {
                ngModel: '=ngModel',
                ngChanges: '&?',
                ngDisabled: '=ngDisabled',
                ngConfig: '=ngConfig',
                editor : '=?editor'
            },
            link: function (scope, elem, attrs) {
                
                elem[0].innerHTML = '<div class="ng-ckeditor"></div> <div class="totalTypedCharacters"></div>';

                var elemEditor = elem[0].querySelectorAll('.ng-ckeditor');
                var config = {
                    uploadUrl: nsjRouting.generate('_uploader_upload_imagens'),
                    disableNativeSpellChecker: false,
                    removePlugins: 'elementspath',
                    removeButtons: 'Source,Save,NewPage,Preview,Print,Templates,Cut,Copy,Paste,PasteText,PasteFromWord,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Strike,Subscript,Superscript,CopyFormatting,Outdent,Indent,Blockquote,CreateDiv,BidiLtr,BidiRtl,Language,Anchor,Image,Flash,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,Styles,Format,BGColor,ShowBlocks,About',
                    extraPlugins: 'youtube,autolink,image2',
                    extraAllowedContent:'table[*];tr[*];td[*];iframe[*]',
                    resize_enabled: false,
                    readOnly: scope.ngDisabled ? scope.ngDisabled : false
                };

                config.toolbarGroups = [
                    { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
                    { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
                    { name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
                    { name: 'forms', groups: [ 'forms' ] },
                    
                    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
                    { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
                    { name: 'links', groups: [ 'links' ] },
                    { name: 'insert', groups: [ 'insert' ] },
                    
                    { name: 'styles', groups: [ 'styles' ] },
                    { name: 'colors', groups: [ 'colors' ] },
                    { name: 'tools', groups: [ 'tools' ] },
                    { name: 'others', groups: [ 'others' ] },
                    { name: 'about', groups: [ 'about' ] }
                ];
                
                if (attrs.removePlugins != undefined) {
                    config.removePlugins = attrs.removePlugins;
                }
                if (attrs.skin != undefined) {
                    config.skin = attrs.skin;
                }
                if (attrs.width != undefined) {
                    config.width = attrs.width;
                }
                if (attrs.permitirplugins) {
                    var array = config.removeButtons.split(",");                    
                    var itens = attrs.permitirplugins.split(",");
                    itens.forEach(function(i){
                        var index = array.indexOf(i);
                        array.splice(index, 1);
                    });
                    config.removeButtons = array.toString();
                }
                
                if (attrs.height != undefined) {
                    config.height = attrs.height;
                }
                if (attrs.resizeEnabled != undefined) {
                    config.resize_enabled = (attrs.resizeEnabled == "false") ? false : true;
                }

                scope.editor = CKEDITOR.appendTo(elemEditor[0], (scope.ngConfig ? scope.ngConfig : config), '');
                
                var onChangeFunction = function (evt) {
                    scope.ngModel = evt.editor.getData();
                        $timeout(function () {
                            scope.ngModel = evt.editor.getData();
                        });
                        if (elem && attrs.msnCount !== undefined) {
                            elem[0].querySelector('.totalTypedCharacters').innerHTML = attrs.msnCount + " " + evt.editor.getData().length;
                        }
                        if(scope.ngChanges && typeof scope.ngChanges === 'function'){

                            //apply and eval
                            scope.$apply(function() { 
                                scope.$eval(scope.ngChanges); 
                            });

                            //apply only
                            scope.$apply(scope.ngChanges);

                            //$parse method, this allows parameters to be passed
                            var invoker = $parse(scope.ngChanges);
                            invoker(scope);
                        }
                };
                
                var onModeFunction = function(evt){
                  if ( this.mode == 'source' ) {
                    var editable = evt.editor.editable();
                    editable.attachListener( editable, 'input', function() {
                      // Handle changes made in the source mode.
                      scope.ngModel = evt.editor.getData();
                      if (elem && attrs.msnCount !== undefined) {
                          elem[0].querySelector('.totalTypedCharacters').innerHTML = attrs.msnCount + " " + evt.editor.getData().length;
                      }
                      if(scope.ngChanges && typeof scope.ngChanges === 'function'){
                          scope.ngChanges(evt.editor.getData());
                      }
                    });
                  }
                };

                var addEventListener = function (editor) {
                    editor.on('change', onChangeFunction);
                    editor.on( 'fileUploadResponse', function(evt) {
                        evt.editor.fire('change');
                    } );
                    editor.on('mode', onModeFunction);
                };

                addEventListener(scope.editor);

                scope.$watch('ngModel', function (value) {
                    if(value !== scope.editor.getData()){
                        scope.editor.setData(value || '');
                    }
                });

                scope.$watch('ngDisabled', function (value) {
                    if (value) {
                        config.readOnly = true;
                    } else {
                        config.readOnly = false;
                    }

                    //editor = CKEDITOR.replace(elemEditor[0], (scope.ngConfig ? scope.ngConfig : config), '');
                    scope.editor.destroy();
                    scope.editor = CKEDITOR.appendTo(elemEditor[0], (scope.ngConfig ? scope.ngConfig : config), '');
                    addEventListener(scope.editor);
                    scope.editor.setData(scope.ngModel);

                });
            }
        };
    }]);
})(window, document);

