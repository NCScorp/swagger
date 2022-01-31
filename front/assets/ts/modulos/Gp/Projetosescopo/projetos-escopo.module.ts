import angular from "angular";
import { GpProjetosescopo } from "./factory";

export const ProjetosescopoModule = angular
.module('ProjetosescopoModule',[])
.service('GpProjetosescopo', GpProjetosescopo)
.name