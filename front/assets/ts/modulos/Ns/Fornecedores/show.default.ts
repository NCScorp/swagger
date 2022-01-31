import angular = require('angular');
import { NsFornecedoresShowShowController } from './../../Ns/Fornecedores/show.form.show';

export class NsFornecedoresShowShowControllerSobrescrito extends NsFornecedoresShowShowController {

  nsContatosFormShow(subentity: any) {
    let parameter = { 'pessoa': this.entity.fornecedor, 'identifier': subentity.contato };
    if ( parameter.identifier ) {
        this.busy = true;
    }
    var modal = this.NsContatosFormShowService.open(parameter, subentity);
    this.busy = false;
  }

}