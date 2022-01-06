function nsjimage(editor, Upload, nsjRouting){

    editor.addCommand('OpenWindow', {exec: function () {
        var input = document.createElement('input');
        input.type = 'file';
        input.click();
        input.onchange = function () {
            
            if (input.files && input.files[0]) {
                
                // Cria o Span Pai, que engloba a imagem e seta suas propriedades.
                var parentSpan = new CKEDITOR.dom.element('span', editor.document);
                parentSpan.setAttribute("tabindex","-1" );
                parentSpan.setAttribute("contenteditable","false");
                parentSpan.setAttribute("data-cke-widget-wrapper","1" );
                parentSpan.setAttribute("data-cke-filter","off" );
                parentSpan.setAttribute("class","cke_widget_wrapper cke_widget_inline cke_widget_image cke_image_nocaption");
                parentSpan.setAttribute("data-cke-display-name","Imagem");
                parentSpan.setAttribute("data-cke-widget-id","0" );
                parentSpan.setAttribute("role","region" );
                parentSpan.setAttribute("aria-label"," Imagem widget");

                // Adiciona a imagem de loading e seta suas propriedades.
                var url = 'data:image/gif;base64,R0lGODlhHwAfALMPAIuLi/r6+qmpqejo6Le3t/b29tzc3EZGRvT09FdXV8fHx3BwcP39/Tk5OfPz8////yH/C05FVFNDQVBFMi4wAwEAAAAh/wtYTVAgRGF0YVhNUDw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTM4IDc5LjE1OTgyNCwgMjAxNi8wOS8xNC0wMTowOTowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDowNGE4NDE4Ni0yZDU4LWRhNDgtODc5NS0xYTUxZDBlNjU2NGYiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RUZEODcxMUE0QzZCMTFFN0FDQjRFNTU2MzgzNzNBRkQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RUZEODcxMTk0QzZCMTFFN0FDQjRFNTU2MzgzNzNBRkQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTcgKFdpbmRvd3MpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6ZWQ2ZjU0ZTYtOTRkYi01ODRiLTg0MGEtYjlmZTE2MjIwNGZkIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjA0YTg0MTg2LTJkNTgtZGE0OC04Nzk1LTFhNTFkMGU2NTY0ZiIvPiA8L3Jk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';
                var newElementImg = new CKEDITOR.dom.element('img', editor.document);
                newElementImg.setAttribute('src', url);
                newElementImg.setAttribute("data-cke-widget-upcasted","1");
                newElementImg.setAttribute("data-cke-widget-keep-attr","0");
                newElementImg.setAttribute("data-widget","image");
                newElementImg.setAttribute("class","cke_widget_element");
                newElementImg.setAttribute("tabIndex","1");

                newElementImg.$.onclick = function() {
                    parentSpan.addClass('cke_widget_focused');
                    parentSpan.addClass('cke_widget_selected');

                    newElementImg.$.focus();
                }

                newElementImg.$.onblur = function() {
                    parentSpan.$.classList.remove('cke_widget_focused');
                    parentSpan.$.classList.remove('cke_widget_selected');
                }

                // Cria um elemento filho para seguir a estrutura do CKEditor.
                var subSpan = new CKEDITOR.dom.element('span', editor.document);
                var urlResize = "http://localhost/bundles/nasajonatendimentoapp/js/ckeditor/plugins/widget/images/handle.png";
                subSpan.setAttribute("class","cke_reset cke_widget_drag_handler_container");
                subSpan.setAttribute("style","background: url('" + urlResize + "') rgba(220, 220, 220, 0.5); top: -15px; left: 0px; display: block;")

                // Cria um elemento filho (quadradinho preto) responsável por iniciar o redimensionamento da imagem.
                var imgSubSpan = new CKEDITOR.dom.element('span', editor.document);
                imgSubSpan.setAttribute("class","cke_reset cke_widget_drag_handler");
                imgSubSpan.setAttribute("style","background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAPBAMAAADJ+Ih5AAAAKlBMVEX///8kJCQmJiYkJCQjIyMlJSUkJCQmJiYlJSUlJSUlJSUlJSUlJSUlJSUyoNtPAAAADXRSTlMAODxASEx/gLjAwsfJ/p3wDgAAAFRJREFUCNdj0Lm7gJ1h7R0FhqMMDLYGDKwHGC4wMMydwACkgAxeBhBjAYQBpCAMBgaWDSDGFgaG3LtgkMDA2AASaWRAqIHrgpsDN/kI2C6WAwww2wEitR7G6bsk5gAAAABJRU5ErkJggg==') rgba(220, 220, 220, 0.5); top: -15px; left: 0px; display: block;");
                imgSubSpan.setAttribute("data-cke-widget-drag-handler","1");
                imgSubSpan.setAttribute("width","15" );
                imgSubSpan.setAttribute("title","Click e arraste para mover");
                imgSubSpan.setAttribute("height","15");
                imgSubSpan.setAttribute("role","presentation");
                imgSubSpan.setAttribute("draggable","true");

                // Adiciona o último span.
                var lastSpan = new CKEDITOR.dom.element('span', editor.document);
                lastSpan.setAttribute("class","cke_image_resizer");
                lastSpan.setAttribute("title","Click e arraste para redimensionar");
                lastSpan.setValue("&#8203");

                // Adiciona os eventos necessários para redimensionar a imagem.
                lastSpan.on('mousedown', function(event) {
                    lastSpan.addClass('cke_image_resizing');

                    if (!parentSpan.$.classList.contains('cke_widget_focused')) {
                        parentSpan.addClass('cke_widget_focused');
                    }

                    if (!parentSpan.$.classList.contains('cke_widget_selected')) {
                        parentSpan.addClass('cke_widget_selected');
                    }

                    // Pega a coordenada x, quando clica no elemento.
                    var x = event.data.$.clientX;

                    // Cria uma flag que habilitará ou não o redimensionamento da imagem.
                    var canResize = true;

                    // Pega o html, elemento pai do span que foi adicionado para adicionar os eventos de mouse.
                    var html = parentSpan.$.parentElement.parentElement.parentElement;

                    // Caso a flag canResize esteja true, seta o atributo width da imagem com o width atualizado.
                    html.onmousemove =  function(event) {
                        if (!canResize) {
                            return;
                        }

                        newElementImg.$.setAttribute('width', newElementImg.$.width + (event.clientX - x));
                        x = event.clientX;
                    };

                    // Seta a flag canResize = false.
                    html.onmouseup = function(event) {
                        canResize = false;
                    };
                });

                // Adiciona os elementos criados para o CKEditor.
                subSpan.append(imgSubSpan);

                parentSpan.append(newElementImg);
                parentSpan.append(subSpan);
                parentSpan.append(lastSpan);

                editor.insertElement(parentSpan);

                editor.fire('isUploading', true);
                Upload.upload({
                    url: nsjRouting.generate('_uploader_upload_imagens'),
                    data: {file: input.files[0]}
                }).then(function (resp) {
                    newElementImg.$.addEventListener('load', function () {
                        editor.fire('isUploading', false);
                    });
                    newElementImg.setAttribute('src', resp.data.url);
                    newElementImg.setAttribute('data-cke-saved-src', resp.data.url);
                    
                    parentSpan.addClass('cke_widget_focused');
                    parentSpan.addClass('cke_widget_selected');
                    parentSpan.addClass('cke_image_resizer_wrapper');

                    editor.fire('change');
                });

            } else {
                alert("Só é permitido o upload de imagens através deste botão. Para upload de arquivos, favor utilizar os anexos.");
            }
        }

        input.value = '';
    }});

    editor.ui.addButton('nsjimage',{
        label: 'Carregar Imagem',
        command: 'OpenWindow',
        toolbar: 'insert',
        icon: 'https://s3-us-west-2.amazonaws.com/static.nasajon/lib/ck-editor/4.5.10/skins/moono/images/nsjimg.png'
    });


    // editor.plugins.add('nsjimage',
    //                 {
    //                     icons: 'https://s3-us-west-2.amazonaws.com/static.nasajon/lib/ck-editor/4.5.10/skins/moono/images/nsjimg.png',
    //                     init: function (editor) {



    //                     }
    //                 });

    // editor.addCommand("mySimpleCommand", { // create named command
    //     exec: function(edt) {
    //         alert(edt.getData());
    //     }
    // });

    // editor.ui.addButton('SuperButton', { // add new button and bind our command
    //     label: "Click me",
    //     command: 'mySimpleCommand',
    //     toolbar: 'insert',
    //     icon: 'https://avatars1.githubusercontent.com/u/5500999?v=2&s=16'
    // });
}