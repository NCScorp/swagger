import * as angular from 'angular';

import { ProjetosRouting } from './projetos.routes';

// Controllers
import { ProjetosIndexController } from './index/projetos-index.controller';
import { ProjetosNewController } from './wizard/projetos-new';
import { ProjetosShowEditController } from './showedit/projetos-showedit.controller';
import { ProjetosDocumentosNecessariosModalController } from './showedit/documentosnecessariosmodal/documentosnecessarios.modal.controller';
import { ModalTemplateController } from './showedit/template/modal/modal-template';
import { ModalExclusaoItemEscopoController } from './showedit/escopo/modalexclusao/modal-exclusao.controller';
import { AdicionarEnderecoEscopoFormController } from './showedit/escopo/adicionarenderecoescopoform/adicionar-endereco.form.controller';
import { VeiculoOcupadoModalController } from './showedit/escopo/veiculoocupadomodal/veiculo-ocupado.modal.controller';
import { FormularioModalController } from './showedit/formularios/formulariomodal/formulario.modal.controller';

// Componentes
import { wizardPassoum } from "./wizard/passoum/wizard.passoum.component";
import { wizardPassodois } from "./wizard/passodois/wizard.passodois.component";
import { wizardPassotres } from "./wizard/passotres/wizard.passotres.component";
import { wizardPassoquatro } from "./wizard/passoquatro/wizard.passoquatro.component";
import { wizardPassocinco } from "./wizard/passocinco/wizard.passocinco.component";
import { ProjetosShowFormComponent } from './showedit/showform/projetos-showform.component';
import { ProjetosShowFormFullComponent } from './showedit/showformfull/projetos-showform-full.component';
import { ProjetosEditFormComponent } from './showedit/editform/projetos-editform.component';
import { EscopoComponent } from './showedit/escopo/escopo.component';
import { AdicionarEnderecoEscopoForm} from './showedit/escopo/adicionarenderecoescopoform/adicionar-endereco.component';
import { FormulariosComponent } from './showedit/formularios/formularios.component';

// Services
import { ProjetosService } from './projetos.service';
import { EscopoService } from './showedit/escopo/escopo.service';
import { ProjetosDocumentosNecessariosModalService } from './showedit/documentosnecessariosmodal/documentosnecessarios.modal.service';
import { ModalExclusaoItemEscopoService } from './showedit/escopo/modalexclusao/modal-exclusao.service';
import { ModalTemplateService } from './showedit/template/modal/modal-template';
import { FormulariosService } from './formularios.service';
import { FormulariosModulosService } from './formularios-modulos.service';
import { OrdensServicosTemplatesService } from './showedit/escopo/odensservicostemplates.service'; 
import { ProjetosTemplatesService } from './showedit/template/projetos-templates.service';
import { VeiculoOcupadoModalService } from './showedit/escopo/veiculoocupadomodal/veiculo-ocupado.modal.service';
import { FormulariosModulosRespostasService } from './formularios-modulos-respostas.service';
import { FormularioModalService } from './showedit/formularios/formulariomodal/formulario.modal.service';

// Submódulos
// Constantes
import { FIELDS_Projetos } from './projetos.constant';

export const ProjetosModule = angular.module('ProjetosModule', ['ui.router.state'])
    .controller('projetosIndexController', ProjetosIndexController)
    .controller('projetosNewController', ProjetosNewController)
    .controller('projetosShowEditController', ProjetosShowEditController)
    .controller('projetosDocumentosNecessariosModalController', ProjetosDocumentosNecessariosModalController)
    .controller('modalTemplateController', ModalTemplateController)
    .controller('modalExclusaoItemEscopoController', ModalExclusaoItemEscopoController)
    .controller('adicionarEnderecoEscopoFormController', AdicionarEnderecoEscopoFormController)
    .controller('veiculoOcupadoModalController', VeiculoOcupadoModalController)
    .controller('formularioModalController', FormularioModalController)
    .service('projetosService', ProjetosService)
    .service('escopoService', EscopoService)
    .service('projetosDocumentosNecessariosModalService', ProjetosDocumentosNecessariosModalService)
    .service('modalTemplateService', ModalTemplateService)
    .service('modalExclusaoItemEscopoService', ModalExclusaoItemEscopoService)
    .service('formulariosService', FormulariosService)
    .service('formulariosModulosService', FormulariosModulosService)
    .service('formulariosModulosRespostasService', FormulariosModulosRespostasService)
    .service('ordensServicosTemplatesService', OrdensServicosTemplatesService)
    .service('projetosTemplatesService', ProjetosTemplatesService)
    .service('veiculoOcupadoModalService', VeiculoOcupadoModalService)
    .service('formularioModalService', FormularioModalService)
    .component('wizardPassoum', wizardPassoum)
    .component('wizardPassodois', wizardPassodois)
    .component('wizardPassotres', wizardPassotres)
    .component('wizardPassoquatro', wizardPassoquatro)
    .component('wizardPassocinco', wizardPassocinco)
    .component('projetosShowFormComponent', ProjetosShowFormComponent)
    .component('projetosShowFormFullComponent', ProjetosShowFormFullComponent)
    .component('projetosEditFormComponent', ProjetosEditFormComponent)
    .component('adicionarEnderecoEscopoForm', AdicionarEnderecoEscopoForm)
    .component('escopoComponent', EscopoComponent)
    .component('formulariosComponent', FormulariosComponent)
    .config(ProjetosRouting)
    .constant('FIELDS_Projetos',FIELDS_Projetos)
    .constant('OPTIONS_Projetos', { 'situacao': 'Situação', })
    .constant('MAXOCCURS_Projetos', {})
    .constant('SELECTS_Projetos', { 'situacao': { '0': 'Aberto', '1': 'Cancelado', '2': 'Em Andamento', '3': 'Finalizado', '4': 'Parado', '6': 'Aguardando Inicialização' }, })
    .run(['$rootScope', 'FIELDS_Projetos', 'OPTIONS_Projetos', 'MAXOCCURS_Projetos', 'SELECTS_Projetos',
        ($rootScope: any, FIELDS_Projetos: object, OPTIONS_Projetos: object, MAXOCCURS_Projetos: object, SELECTS_Projetos: object) => {
        $rootScope.FIELDS_Projetos = FIELDS_Projetos;
        $rootScope.OPTIONS_Projetos = OPTIONS_Projetos;
        $rootScope.MAXOCCURS_Projetos = MAXOCCURS_Projetos;
        $rootScope.SELECTS_Projetos = SELECTS_Projetos;
    }]).name;
