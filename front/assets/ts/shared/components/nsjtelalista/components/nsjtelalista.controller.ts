import { NsjTelaListaClasses } from "./nsjtelalista.classes";
import { ServiceBase } from "../../../services/service-base/service-base.service";

export class NsjTelaListaController {
    static $inject = ['$scope'];

    /**
     * Configurações do componente
     */
    public config: NsjTelaListaClasses.Config;
    /**
     * Lista de dados
     */
    public entities: any[] = [];
    /**
     * Service utilizado na tela
     */
    public service: ServiceBase<any>;

    /**
     * Enumerado de tipos de tela de listagem, para utilizar na view
     */
    ETipoTela = NsjTelaListaClasses.ETipoTela;

    constructor (
        public $scope: any
    ) {}

    $onInit() {}
}