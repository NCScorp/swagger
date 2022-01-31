import angular from "angular";
import { ServicosPessoasmunicipios } from "./factory";

export const PessoasmunicipiosModule =  angular
.module('PessoasmunicipiosModule',[])
.service('ServicosPessoasmunicipios', ServicosPessoasmunicipios)
.name