angular.module('mda').controller('AtendimentoHorariosatendimentoFormController', ['$scope', '$stateParams', '$state', 'AtendimentoHorariosatendimento', 'entity', 'toaster',
function($scope, $stateParams, $state, entityService, entity, toaster) {  
    var self = this;
    self.submitted = false;
    self.entity = entity;

    if (!self.entity) {
        self.entity = { };
    }

    Object.keys(self.entity).forEach(function(dia) {
        // Verificando o dia da semana que tem valor = true
        if((self.entity[dia] === true) && (self.entity[dia + '_inicio_expediente'] !== null) && (self.entity[dia + '_inicio_intervalo'] !== null) && (self.entity[dia + '_fim_intervalo'] !== null) && (self.entity[dia + '_fim_expediente'] !== null)) {
            var horarios = [
                self.entity[dia + '_inicio_expediente'],
                self.entity[dia + '_inicio_intervalo'],
                self.entity[dia + '_fim_intervalo'],
                self.entity[dia + '_fim_expediente']
            ];

            // Alterando os valores recebidos para o tipo Date
            for(var c = 0; c < horarios.length; c++) {
                var novaData = new Date();
                var splitted = horarios[c].split(":");
                novaData.setHours(splitted[0], splitted[1], 0, 0);
                horarios[c] = novaData;
            }

            // Sobrescrevendo os horários com as datas formadas
            self.entity[dia + '_inicio_expediente'] = horarios[0];
            self.entity[dia + '_inicio_intervalo'] = horarios[1];
            self.entity[dia + '_fim_intervalo'] = horarios[2];
            self.entity[dia + '_fim_expediente'] = horarios[3];
        }
    });

    self.constructors = {};

    self.submit = function () {
        self.submitted = true;
        
        if(self.form.$valid && !self.entity.$$__submitting) {

            var date = (new Date()).toLocaleDateString('PT-Br') + ' ';

            var valido = true;

            // Variável para armazenar os horários válidos
            var horariosValidos = [];

            // Recebe as 'keys' do objeto e faz um loop nele
            Object.keys(self.entity).forEach(function(diaSemana) {

                if (!valido) {
                    return;
                }

                // Verificando o dia da semana que tem valor = true
                if(self.entity[diaSemana] === true) {
                    // Sobrescrevendo as datas, do dia em questão, sendo tratadas para ficarem apenas com horas e minutos

                    // Correção da data e hora considerando o Timezone.
                    var inicioExpediente = date + self.entity[diaSemana + '_inicio_expediente'].toLocaleTimeString('pt-br');
                    inicioExpediente = moment(inicioExpediente,'YYYY-MM-DD HH:mm').utc(inicioExpediente).tz(self.entity.timezone).toDate();

                    var inicioIntervalo = date + self.entity[diaSemana + '_inicio_intervalo'].toLocaleTimeString('pt-br');
                    inicioIntervalo = moment(inicioIntervalo,'YYYY-MM-DD HH:mm').utc(inicioIntervalo).tz(self.entity.timezone).toDate();

                    var fimIntervalo = date + self.entity[diaSemana + '_fim_intervalo'].toLocaleTimeString('pt-br');
                    fimIntervalo = moment(fimIntervalo,'YYYY-MM-DD HH:mm').utc(fimIntervalo).tz(self.entity.timezone).toDate();

                    var fimExpediente = date + self.entity[diaSemana + '_fim_expediente'].toLocaleTimeString('pt-br');
                    fimExpediente = moment(fimExpediente,'YYYY-MM-DD HH:mm').utc(fimExpediente).tz(self.entity.timezone).toDate();

                    valido = !((inicioExpediente >= inicioIntervalo || inicioExpediente >= fimIntervalo || inicioExpediente >= fimExpediente) ||
                               (inicioIntervalo > fimIntervalo || inicioIntervalo >= fimExpediente) ||
                               (fimIntervalo >= fimExpediente));

                    if (!valido) {
                        toaster.error('Os horários de ' + ((diaSemana != 'sabado' && diaSemana != 'domingo') ? diaSemana.replace('c', 'ç') + '-feira' : diaSemana.replace('a', 'á')) + ' estão incorretos!');

                        return;
                    }

                    // Seta os horários convertidos para a variável horariosValidos ao invés de passar para a entidade.
                    // Isso é necessário porque o moment altera os horários de acordo com o timezone.
                    // Então, se houver algum erro de validação, os horários sempre são alterados de acordo com o moment, fazendo a entidade ficar com horários errados.
                    horariosValidos[diaSemana + '_inicio_expediente'] = inicioExpediente;
                    horariosValidos[diaSemana + '_inicio_intervalo'] = inicioIntervalo;
                    horariosValidos[diaSemana + '_fim_intervalo'] = fimIntervalo;
                    horariosValidos[diaSemana + '_fim_expediente'] = fimExpediente;
                
                } else {
                    horariosValidos[diaSemana + '_inicio_expediente'] = null;
                    horariosValidos[diaSemana + '_inicio_intervalo'] = null;
                    horariosValidos[diaSemana + '_fim_intervalo'] = null;
                    horariosValidos[diaSemana + '_fim_expediente'] = null;
                }
            });

            if (valido) {

                // Seta os horários válidos par a entidade
                Object.keys(horariosValidos).forEach(function(horario) {

                    self.entity[horario] = horariosValidos[horario];
                });

                entityService.save(self.entity);
            }
        } else {
            toaster.pop({type: 'error', title: 'Alguns campos do formulário apresentam erros.'});
        }
    };

    self.onSubmitSuccess = $scope.$on("atendimento_horariosatendimento_submitted", function (event, args) {
        var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
        toaster.pop({type: 'success', title: 'Sucesso ao ' + acao + ' Horários de Atendimento!'});
        $state.go('atendimento_admin_configuracoes_list');
    });

    self.onSubmitError = $scope.$on("atendimento_horariosatendimento_submit_error", function (event, args) {
        if (args.response.status == 409) {
            if (confirm(args.response.data.message)) {
                self.entity[''] = args.response.data.entity[''];
                entityService.save(self.entity);
            }
        } else {
            if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
                toaster.pop({type: 'error', title: args.response.data.message});
            } else {
                var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
                toaster.pop({type: 'error', title: "Ocorreu um erro ao tentar " + acao + "."});
            }
        }
    });

    for (var i in $stateParams) {
        if (i != 'entity') {
            self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
        }
    }
}]);