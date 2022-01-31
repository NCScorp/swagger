import angular = require('angular');

export class DashboardatendimentospendenciasController {
    static $inject = ['$scope'];

    public dashboardInterval;

    constructor(public $scope: ng.IScope) {
      $scope.$watch('$destroy', () => {
        clearInterval(this.dashboardInterval);
      });
    }

    /**
     * Utilizado para controllar o carregamento nas telas filhas
     */
    ctrlReqChild: {
        fnLoadAtendimentos: () => Promise<any>;
        fnLoadPendencias: () => Promise<any>;
    } = {
        fnLoadAtendimentos: null,
        fnLoadPendencias: null
    }

    $onInit(){
        this.atualizaDashboard();
    }

    async atualizaDashboard(){
        try {

            //se as funções ainda não tiverem sido injetadas, reprogramo um atraso de 1 sec para re-execução
            if(this.ctrlReqChild.fnLoadPendencias == null || this.ctrlReqChild.fnLoadAtendimentos == null) {
              this.dashboardInterval = setTimeout(() => {
                  this.atualizaDashboard();
                }, 1000)
                return;
            }

            await Promise.all([
                this.ctrlReqChild.fnLoadAtendimentos(), this.ctrlReqChild.fnLoadPendencias()
            ]);
        } catch (error) {
          console.error(error);
        }
        finally {
          this.dashboardInterval = setTimeout(() => {
              this.atualizaDashboard();
            }, 300000)
        }
    }
 
}
