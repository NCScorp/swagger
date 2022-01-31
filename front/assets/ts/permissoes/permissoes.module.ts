import * as angular from 'angular';
import { TemPermissaoDirective } from './permissoes.directive';



export const permissoes = angular.module('permissoes', ['ui.router'])
.directive('temPermissao', TemPermissaoDirective.Factory())
.name




