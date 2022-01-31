import angular from "angular";
import { NsHistoricofornecedores } from "./factory";

export const HistoricofornecedoresModule =  angular
.module('HistoricofornecedoresModule',[])
.service('NsHistoricofornecedores', NsHistoricofornecedores)
.name