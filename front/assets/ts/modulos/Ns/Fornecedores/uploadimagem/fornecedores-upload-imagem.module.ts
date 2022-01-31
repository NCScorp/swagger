import angular from "angular";
import { FornecedoresImagemModalService } from "./modal";
import { FornecedoresImagemModal } from "./modaluploadimagem";

export const FornecedoresUploadImagemModule = angular
    .module('FornecedoresUploadImagemModule', [])
    .service('FornecedoresImagemModalService', FornecedoresImagemModalService)
    .controller('FornecedoresImagemModal', FornecedoresImagemModal)
    .name