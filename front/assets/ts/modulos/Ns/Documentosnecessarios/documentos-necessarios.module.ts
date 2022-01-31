import angular from "angular";
import { nsDocumentosnecessariosDefault } from "./components";
import { NsDocumentosnecessariosDefaultController } from "./default.form";
import { NsDocumentosnecessarios } from "./factory";

export const DocumentosnecessariosModule = angular
    .module('DocumentosnecessariosModule', [])
    .component('nsDocumentosnecessariosDefault', nsDocumentosnecessariosDefault)
    .controller('NsDocumentosnecessariosDefaultController', NsDocumentosnecessariosDefaultController)
    .service('NsDocumentosnecessarios', NsDocumentosnecessarios)
    .name