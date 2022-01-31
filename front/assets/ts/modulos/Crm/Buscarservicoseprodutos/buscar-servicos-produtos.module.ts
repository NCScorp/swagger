import angular from "angular";
import { CrmBuscarservicoseprodutos } from "./factory";

export const BuscarservicoseprodutosModule = angular
.module('BuscarservicoseprodutosModule',[])
.service('CrmBuscarservicoseprodutos', CrmBuscarservicoseprodutos)
.name