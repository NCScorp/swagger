import angular = require('angular');
export class NsClientesFormNewController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'NsClientes',
        'toaster',
        'entity',
        'WebConfiguracoes'
    ];
    public action: string = 'insert';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
        constructor(                                                                                                                                                                        public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any,
    public WebConfiguracoes: any ) {
                this.constructors = entityService.constructors;
                        $scope.$on('ns_clientes_submitted', (event: any, args: any) => {
                            this.toaster.pop({
                    type: 'success',
                    title: 'O Cliente foi criado com sucesso!'
                });
                this.$state.go('ns_clientes_show', angular.extend({}, this.entityService.constructors, {'cliente': args.entity.cliente}));
                    });
        $scope.$on('ns_clientes_submit_error', (event: any, args: any) => {
                            if (args.response.status === 409) {
        if (confirm(args.response.data.message)) {
            this.entity[''] = args.response.data.entity[''];
            this.entityService.save(this.entity);
        }
    } else {
       if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
            if ( args.response.data.message === 'Validation Failed' ) {
                let distinct = (value: any, index: any, self: any) => {
                    return self.indexOf(value) === index;
                };
                args.response.data.errors.errors = args.response.data.errors.errors.filter(distinct);
                let message = this.utilService.parseValidationMessage(args.response.data);
                this.toaster.pop(
                {
                    type: 'error',
                    title: 'Erro de Validação',
                    body: 'Os seguintes itens precisam ser alterados: <ul>' + message + '</ul>',
                    bodyOutputType: 'trustedHtml'
                });
            } else {
                this.toaster.pop(
                {
                    type: 'error',
                    title: args.response.data.message
                });
            }
        } else {
            this.toaster.pop(
            {
                type: 'error',
                title: 'Ocorreu um erro ao criar o Cliente!'            });
        }
    }

        });


        this.$scope.$on('web_configuracoes_list_finished', (event: any, args: any) => {
            this.entity.diasparavencimento =  this.entity.diasparavencimento ? this.entity.diasparavencimento : args.length > 0 ? args[0]['valor'] : null ;
          });  
  
          WebConfiguracoes.constructors.chave =  'DIASPARAVENCIMENTO';
          let list = WebConfiguracoes.load();
    }

  /**
     * Sobrescrito para validar o campo diasparavencimento e tipos de endereço
     */
    submit(){
        if (this.form.$valid) {

            //Validando tipos de endereço
            if (this.entity.endereco != null && this.entity.endereco.length > 1){

                //Verificando se há tipo de endereço local ou cobrança duplicados
                let qtdTiposEnd = this.validaTipoEnderecoDuplicado(this.entity.endereco);
                let msgToasterErro;
                let erroEndereco: boolean = false;
    
                //Escolhendo a mensagem apropriada para exibir
                if(qtdTiposEnd.qtdEndLocal > 1 && qtdTiposEnd.qtdEndCobranca > 1){
                    erroEndereco = true;
                    msgToasterErro = 'Não é possível ter mais de um endereço de cobrança e mais de um endereço local. Verifique!'
                }
                else if(qtdTiposEnd.qtdEndLocal > 1 || qtdTiposEnd.qtdEndCobranca > 1){
                    erroEndereco = true;
                    msgToasterErro = qtdTiposEnd.qtdEndLocal > 1 
                        ? 'Não é possível ter mais de um endereço local. Verifique!' 
                        : 'Não é possível ter mais de um endereço de cobrança. Verifique!';
                }
    
                //Disparando toaster
                if(erroEndereco){
                    this.toaster.pop({
                        type: 'error',
                        title: msgToasterErro
                    });
                    return;
                }
            }

            this.form.$valid = this.validarDiasParaVencimento();
        }
  
        else if(!this.form.$valid){
          // Se o form não for válido, verifico se o problema está no campo diasparavencimento para retornar a mensagem tratada
          if (this.entity.diasparavencimento <= 0 || this.entity.diasparavencimento === null || this.entity.diasparavencimento === undefined) {
            this.toaster.pop({
                type: 'error',
                title: 'O campo Vencimento do pagamento possui um valor inválido!'
            });
          }
          // Se o problema não for o campo diasparavencimento, chamo o submit da superclasse
          else{
            return this.superSubmit();
          }
        }
  
        if (this.form.$valid) {
            return this.superSubmit();
        } 
    }

    superSubmit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {

            /**
             * TO DO: Quando o front end de Clientes estiver apontando para o ErpApi, essa verificação não será mais necessária.
             * Remover essa verificação e checar se o endereço é salvo corretamente no Cliente
             */

            // Se o Cliente possuir endereço, faço a verificação
            if(this.entity.endereco != null && this.entity.endereco.length > 0){

                this.entity.endereco.forEach(endereco => {
                    
                    // Se não possuir o campo codigo setado e possuir ibge, uso o ibge para definir o campo codigo
                    if(endereco.municipio.codigo == null && endereco.municipio.ibge != null){
                        endereco.municipio.codigo = endereco.municipio.ibge;
                    }

                });

            }

            this.entityService.save(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }

    /**
     * Verificando se o campo diasparavencimento possui um valor válido
     */
    validarDiasParaVencimento(): boolean {
        let valido: boolean = true;

        if (this.entity.diasparavencimento >= 2147483648) {
            valido = false;
            this.toaster.pop({
                type: 'error',
                title: 'O campo Vencimento do pagamento não pode ser maior que 2147483648 dias.'
            });
        }

        return valido;
    }

    /**
     * Retorna um objeto com a quantidade de endereços do tipo local e cobrança
     */
    validaTipoEnderecoDuplicado(enderecos: any){

        //Arrays de endereços do tipo local e cobrança
        let contEndLocal = [];
        let contEndCobranca = [];

        contEndLocal = enderecos.filter((endereco) => {
            return endereco.tipoendereco == 0;
        });

        contEndCobranca = enderecos.filter((endereco) => {
            return endereco.tipoendereco == 2;
        });

        return {
            qtdEndLocal: contEndLocal.length,
            qtdEndCobranca: contEndCobranca.length
        }

    }

}
