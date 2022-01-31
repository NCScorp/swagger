<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use LogicException;
use Nasajon\AppBundle\Entity\Crm\ValidacaoNegocioListaDaVezConfiguracao;
use Nasajon\MDABundle\Service\Crm\ListadavezconfiguracoesService as ParentService;

/**
* ListadavezconfiguracoesService
*
*/
class ListadavezconfiguracoesService extends ParentService
{
    /**
     * Array de listadavezregras
     */
    private $arrRegras = [];
    /**
     * Service de lista da vez regras
     */
    private $lstvzrgsrvc = null;
    /**
     * Construtor tenant
     */
    private $tenant;
    /**
     * Construtor id_grupoempresarial
     */
    private $id_grupoempresarial;

    // Constantes com o nome das regras fixas
    const REGRA_FIXA_JA_E_CLIENTE = 'Já é Cliente?';
    const REGRA_FIXA_FATURAMENTO_ANUAL = 'Faturamento Anual';
    const REGRA_FIXA_CARGO_CONTATO = 'Cargo do Contato';

    // Constantes com o tipo de regra
    const REGRA_TIPO_FIXA = 0;
    const REGRA_TIPO_UF = 1;
    const REGRA_TIPO_OPERACAO = 2;
    const REGRA_TIPO_SEGMENTO = 3;

    // Constantes com o tipo da configuração
    const CONFIGURACAO_TIPO_REGRA = 0;
    const CONFIGURACAO_TIPO_OPCAO = 1;
    const CONFIGURACAO_TIPO_FLUXO_ALTERNATIVO = 2;

    public function __construct(
        \Nasajon\MDABundle\Repository\Crm\ListadavezconfiguracoesRepository $repository,
        $lstvzrgsrvc
    ){
        parent::__construct($repository);

        $this->lstvzrgsrvc = $lstvzrgsrvc;
    }

    /**
     * Salva a configuração de lista da vez
     */
    public function salvarListaDaVezConfiguracao($tenant, $logged_user, $id_grupoempresarial, \Nasajon\AppBundle\Entity\Crm\ListadavezconfiguracoesEmLote $entity, \Nasajon\AppBundle\Entity\Crm\ListadavezconfiguracoesEmLote $entityOriginal){
        // Seto construtores
        $this->tenant = $tenant;
        $this->id_grupoempresarial = $id_grupoempresarial;

        try {
            $this->getRepository()->begin();
            
            $this->persistChildListadavezconfiguracoes(
                $entityOriginal->getListadavezconfiguracoes()->toArray(),
                $entity->getListadavezconfiguracoes()->toArray(), 
                $entity, $logged_user, $tenant, $id_grupoempresarial
            );
                                                                
            $this->getRepository()->commit();
        }catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        }
    }
 
    /**
     * Persiste no banco todas as configurações, respeitando o modelo de árvore para validação, 
     *  exclusão, alteração e inclusão no banco de dados
     */
    protected function persistChildListadavezconfiguracoes($oldList, $newList, $entity, $logged_user, $tenant, $id_grupoempresarial) {
        /**
         * Lista de itens que serão alterados
         */
        $listaAlterar = [];
        /**
         * Lista de itens que serão removidos
         */
        $listaRemover = [];

        // Preencho listas de alterar e remover
        $newIds = array_map(function ($entity) {
            return $entity->getListadavezconfiguracao();
        }, $newList);

        foreach ($oldList as $item) {
            $id = $item->getListadavezconfiguracao();
            $index = array_search($id, $newIds);
            
            if ($index === false) {
                $listaRemover[] = $id;
            } else {
                $listaAlterar[] = [
                    'id' => $id,
                    'index' => $index
                ];
            }
        }

        // Monto árvore dos itens antigos
        $arrArvoreVelha = $this->montaArvore(null, $oldList);
        $arvoreVelha = count($arrArvoreVelha) > 0 ? $arrArvoreVelha[0] : null;

        // Monto árvore de novos itens
        $arrArvoreNova = $this->montaArvore(null, $newList);
        $arvoreNova = count($arrArvoreNova) > 0 ? $arrArvoreNova[0] : null;
        
        // Valido nova árvore
        if (count($arrArvoreNova) == 0) {
            throw new LogicException("A lista da vez deve possuir uma configuração inicial.", 1);
        } else if (count($arrArvoreNova) > 1) {
            throw new LogicException("A lista da vez deve possuir apenas uma configuração inicial", 1);
        }

        $this->validarArvore($arvoreNova, $tenant, $id_grupoempresarial);

        if ($arvoreVelha != null) {
            // Chamo alteração dos itens
            foreach ($listaAlterar as $itemAlterar) {
                $objItemAlterar = $newList[$itemAlterar['index']];
                $this->update($tenant, $id_grupoempresarial, $logged_user, $objItemAlterar);
            }
    
            // Chamo exclusão em árvore dos itens
            $this->excluirEmArvore($arvoreVelha, $listaRemover, $tenant, $id_grupoempresarial, $logged_user);
        }

        // Chamo adição em árvore dos itens
        $this->adicionarEmArvore($arvoreNova, $tenant, $id_grupoempresarial, $logged_user);
    }

    /**
     * Valida a nova árvore de acordo com algumas regras de negócio
     */
    private function validarArvore($arvore, $tenant, $id_grupoempresarial){
        // Valido tipos dos filhos, quantidades de filhos que se pode ter e regras de valor dos filhos
        // Para cada erro vem um código. Se retornar 0, significa sem erros
        $validacaoTipos = $this->validarArvoreTipoFilhosCorretos($arvore, $tenant, $id_grupoempresarial, true);
        if ($validacaoTipos['codigo'] > 0) {
            throw new LogicException($validacaoTipos['erro'], 1);
        }
        
        // Valido duplicidade de regras nas configurações
        if ($this->validarArvoreRegraEstaDuplicada($arvore)){
            throw new LogicException("A lista da vez possui configurações com regras duplicadas.", 1);
        }

        // Valido duplicidade de valores para uma regra nas configurações
        if ($this->validarArvoreRegraValorEstaDuplicada($arvore, $tenant, $id_grupoempresarial)){
            throw new LogicException("A lista da vez possui configurações com valores duplicados para a mesma regra.", 1);
        }
    }

    /**
     * Verifica se uma regra utilizada por uma configuração pai está sendo utilizada por configurações de regras filhas
     * Retorna true se o registro possuir regra duplicada
     */
    private function validarArvoreRegraEstaDuplicada($arvore, $regras=[]) {
        $regra = $arvore['entity']->getListadavezregra()->getListadavezregra();
        // Se regra já está sendo utilizada e a configuração é do tipo 0 - "Regra"
        $regraDuplicada = in_array($regra, $regras) && $arvore['entity']->getTiporegistro() == self::CONFIGURACAO_TIPO_REGRA;

        // Se a regra está duplicada, retorno false
        if ($regraDuplicada) {
            return true;
        } else {
            $regras[] = $regra;
            
            // Verifico se os filhos possuem regras já utilizadas
            for ($i=0; $i < count($arvore['filhos']); $i++) {
                $itemArvoreFilho = $arvore['filhos'][$i];
                
                $regraFilhoDuplicada = $this->validarArvoreRegraEstaDuplicada($itemArvoreFilho, $regras);

                if ($regraFilhoDuplicada) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verifica se um valor da regra está sendo utilizado mais de uma vez
     * Retorna true se o registro possuir valor de regra duplicado
     */
    private function validarArvoreRegraValorEstaDuplicada($arvore, $tenant, $id_grupoempresarial) {
        $tipoRegistro = $arvore['entity']->getTiporegistro();
        $regra = $arvore['entity']->getListadavezregra()->getListadavezregra();
        $regraObj = null;

        if ($tipoRegistro == self::CONFIGURACAO_TIPO_REGRA) {
            // Busco regra no banco de dados
            $regraObj = $this->findRegra($regra, $tenant, $id_grupoempresarial);
        }
        
        // Se for registro do tipo regra, procuro nos filhos pra ver se tem o mesmo valor
        $valoreFilhos = [];
        for ($i=0; $i < count($arvore['filhos']); $i++) {
            $itemArvoreFilho = $arvore['filhos'][$i];
            
            // Faço validação por toda a árvore
            $regraValorFilhoDuplicada = $this->validarArvoreRegraValorEstaDuplicada($itemArvoreFilho, $tenant, $id_grupoempresarial);

            if ($regraValorFilhoDuplicada) {
                return true;
            }

            // Se o registro pai for do tipo regra, valido valor dos filhos tipo 1 - Opção
            if ($tipoRegistro == self::CONFIGURACAO_TIPO_REGRA && $itemArvoreFilho['entity']->getTiporegistro() == self::CONFIGURACAO_TIPO_OPCAO){
                $valor = $this->getConfiguracaoOpcaoValor($regraObj, $itemArvoreFilho['entity']);
                
                $regraValorFilhoDuplicada = in_array($valor, $valoreFilhos);

                if ($regraValorFilhoDuplicada) {
                    return true;
                } else {
                    $valoreFilhos[] = $valor;
                }
            }
        }

        return false;
    }

    /**
     * Verifica se os tipos estão de acordo com o pai e faz validação de valores
     * Retorna código 0 se o registro for válido.
     * Se não for válido, retorna um código acima de 0 e uma mensagem de erro
     */
    private function validarArvoreTipoFilhosCorretos($arvore, $tenant, $id_grupoempresarial, $configInicial = false) {
        $tipoRegistro = $arvore['entity']->getTiporegistro();
        $regra = $arvore['entity']->getListadavezregra()->getListadavezregra();
        $regraObj = $this->findRegra($regra, $tenant, $id_grupoempresarial);
        // Defino variável de retorno
        $retorno = [
            'codigo' => 0
        ];
        
        // Utilizado para validar se a regra atingiu o máximo de opções e não precisa de caso alternativo
        $qtdOpcoes = 0;
        // Utilizado para validar a quantidade de regras filhas que uma opção tem
        $qtdRegras = 0;
        // Utilizado para validar a quantidade de casos alternativos que a regra tem
        $qtdCasoAlternativo = 0;
        // Utilizado para validar quando um filho opção não tem o valor preenchido
        $qtdFilhoOpcaoSemValorPreenchido = 0;

        for ($i=0; $i < count($arvore['filhos']); $i++) {
            $itemArvoreFilho = $arvore['filhos'][$i];
            
            // Faço validação por toda a árvore
            $validacaoFilhos = $this->validarArvoreTipoFilhosCorretos($itemArvoreFilho, $tenant, $id_grupoempresarial);

            if ($validacaoFilhos['codigo'] > 0) {
                return $validacaoFilhos;
            }

            // Se o pai for do tipo regra e tiver uma regra diferente do filho
            $regraFilho = $itemArvoreFilho['entity']->getListadavezregra()->getListadavezregra();
            
            if ($tipoRegistro == self::CONFIGURACAO_TIPO_REGRA && $regra != $regraFilho){
                return [
                    'codigo' => 13,
                    'erro' => 'Opções/Casos alternativos devem ter a mesma regra dos pais'
                ];
            }

            // Verifico o tipo de filho
            switch ($itemArvoreFilho['entity']->getTiporegistro()) {
                // Regra
                case self::CONFIGURACAO_TIPO_REGRA: {
                    $qtdRegras++;
                    break;
                }
                // Opção
                case self::CONFIGURACAO_TIPO_OPCAO: {
                    $qtdOpcoes++;
                    if (!$this->configuracaoOpcaoTemValorPreenchido($regraObj, $itemArvoreFilho['entity'])) {
                        $qtdFilhoOpcaoSemValorPreenchido++;
                    }
                    break;
                }
                // Caso alternativo
                case self::CONFIGURACAO_TIPO_FLUXO_ALTERNATIVO: {
                    $qtdCasoAlternativo++;
                    break;
                }
            }
        }

        // Se o registro inicial não for do tipo regra, retorno erro
        if ($configInicial && $tipoRegistro != self::CONFIGURACAO_TIPO_REGRA) {
            
            return [
                'codigo' => 1,
                'erro' => 'A configuração inicial deve ser do tipo regra.'
            ];
        }

        // Se o pai for do tipo 0 (Regra)
        if ($tipoRegistro == self::CONFIGURACAO_TIPO_REGRA) {
            // Se a regra não for do tipo 0 (Valor fixo) e não tiver casos alternativos, retorno erro
            if ($regraObj->getTipoentidade() != self::REGRA_TIPO_FIXA && $qtdCasoAlternativo == 0) {
                return [
                    'codigo' => 2,
                    'erro' => 'A regra ' . $regraObj->getNome() . ' não tem caso alternativo.'
                ];
            }

            // Se a regra for do tipo 0 (Valor fixo), nao tiver o máximo de valores e não tiver um caso alternativo
            if ($regraObj->getTipoentidade() == self::REGRA_TIPO_FIXA && $qtdOpcoes < $regraObj->getTotalvalores() && $qtdCasoAlternativo == 0) {
                return [
                    'codigo' => 3,
                    'erro' => 'A regra ' . $regraObj->getNome() . ' não tem caso alternativo.'
                ];
            }

            // Se houver algum filho opção sem valor preenchido.
            if ($qtdFilhoOpcaoSemValorPreenchido > 0){
                // Se regra for do tipo 0 (Fixa) e o listadavezregravalor não estiver preenchido
                if ($regraObj->getTipoentidade() == self::REGRA_TIPO_FIXA) {
                    return [
                        'codigo' => 14,
                        'erro' => 'A regra ' . $regraObj->getNome() . ' tem filhos sem valor selecionado.'
                    ];
                }

                // Se regra for do tipo 1 (Estado) e o estado não estiver preenchido
                if ($regraObj->getTipoentidade() == self::REGRA_TIPO_UF) {
                    return [
                        'codigo' => 10,
                        'erro' => 'A regra ' . $regraObj->getNome() . ' tem filhos sem valor selecionado.'
                    ];
                }

                // Se regra for do tipo 2 (Negócio operação) e o negocio operação não estiver preenchido
                if ($regraObj->getTipoentidade() == self::REGRA_TIPO_OPERACAO) {
                    return [
                        'codigo' => 11,
                        'erro' => 'A regra ' . $regraObj->getNome() . ' tem filhos sem valor selecionado.'
                    ];
                }

                // Se regra for do tipo 3 (Segmento atuação) e o segmento de atuação não estiver preenchido
                if ($regraObj->getTipoentidade() == self::REGRA_TIPO_SEGMENTO) {
                    return [
                        'codigo' => 12,
                        'erro' => 'A regra ' . $regraObj->getNome() . ' tem filhos sem valor selecionado.'
                    ];
                }
            }

            // Se houver mais de um caso alternativo
            if ($qtdCasoAlternativo > 1) {
                return [
                    'codigo' => 4,
                    'erro' => 'A regra ' . $regraObj->getNome() . ' possui mais de um caso alternativo.'
                ];
            }

            // Se houver filhos tipo regras
            if ($qtdRegras > 0) {
                return [
                    'codigo' => 5,
                    'erro' => 'A regra ' . $regraObj->getNome() . ' possui filhos do tipo regra.'
                ];
            } 
        } 
        // Se o pai for do tipo 1 (Opção) ou 2(Caso alternativo)
        else {
            // Se tiver um filho tipo 1 (Opção)
            if ($qtdOpcoes > 0) {
                return [
                    'codigo' => 6,
                    'erro' => 'Opções/Casos alternativos não podem ter filhos do tipo opção.'
                ];
            }

            // Se tiver um filho tipo 2 (Caso alternativo)
            if ($qtdCasoAlternativo > 0) {
                return [
                    'codigo' => 7,
                    'erro' => 'Opções/Casos alternativos não podem ter filhos do tipo caso alternativo.'
                ];
            }

            // Se não tiver regras filhas e não tiver vendedorfixo marcado e não tem listadavezvendedor preenchido
            if ($qtdRegras == 0 && !$arvore['entity']->getVendedorfixo() && $arvore['entity']->getListadavezvendedor() == null) {
                return [
                    'codigo' => 8,
                    'erro' => 'Opções/Casos alternativos que não tem regras filhas, devem ter vendedor fixo marcado ou uma lista da vez selecionada.'
                ];
            }

            // Se tiver mais de um filho
            if ($qtdRegras > 1) {
                return [
                    'codigo' => 9,
                    'erro' => 'Opções/Casos alternativos só podem ter uma regra filha.'
                ];
            }
        }

        return $retorno;
    }

    /**
     * Busca no banco uma regra de lista da vez e adiciona ao array arrRegras.
     * Caso a regra já esteja no arrRegras, não é feito a busca no banco de dados
     */
    private function findRegra($regra, $tenant, $id_grupoempresarial){
        // Se ainda não busquei a regra, busco no banco de dados
        if (!array_key_exists($regra, $this->arrRegras)) {
            $regraArr = $this->lstvzrgsrvc->find($regra, $tenant, $id_grupoempresarial);
            $regraObj = $this->lstvzrgsrvc->fillEntity($regraArr);
            $this->arrRegras[$regra] = $regraObj;
        }

        return $this->arrRegras[$regra];
    }

    /**
     * Verifica se a opção tem valor preenchido, de acordo com o tipo de regra
     */
    private function configuracaoOpcaoTemValorPreenchido($regraObj, $configuracaoOpcao){
        // Busco valor de acordo com o tipo da regra
        switch ($regraObj->getTipoentidade()) {
            // Valor fixo
            case self::REGRA_TIPO_FIXA: {
                return $configuracaoOpcao->getIdlistadavezregravalor() != null;
                break;
            }

            // Estado
            case self::REGRA_TIPO_UF: {
                return $configuracaoOpcao->getIdestado() != null;
                break;
            }

            // Negócio operação
            case self::REGRA_TIPO_OPERACAO: {
                return $configuracaoOpcao->getIdnegociooperacao() != null;
                break;
            }

            // Segmento de atuação
            case self::REGRA_TIPO_SEGMENTO: {
                return $configuracaoOpcao->getIdsegmentoatuacao() != null;
                break;
            }

            default: {
                return false;
            }
        }
    }

    /**
     * Pega o valor de uma opçao baseado no tipo de regra.
     *  - Tipo 0 (Valor fixo): Pega o valor de listadavezregravalor
     *  - Tipo 1 (Estado): Pega o valor de idestado->uf
     *  - Tipo 2 (Negócio Operação): Pega o valor de idnegociooperacao->proposta_operacao
     *  - Tipo 3 (Segmento de atuação): Pega o valor de idsegmentoatuacao->segmentoatuacao
     */
    private function getConfiguracaoOpcaoValor($regraObj, $configuracaoOpcao){
        $valor = null;

        // Busco valor de acordo com o tipo da regra
        switch ($regraObj->getTipoentidade()) {
            // Valor fixo
            case self::REGRA_TIPO_FIXA: {
                $listadavezregravalor = $configuracaoOpcao->getIdlistadavezregravalor()->getListadavezregravalor();
                $listadavezregravalorObj = null;

                // Busco objeto listadavezregravalor
                $listadavezregravalores = $regraObj->getItens();
                for ($i=0; $i < count($listadavezregravalores); $i++) { 
                    $itemRegraValor = $listadavezregravalores[$i];

                    if ($itemRegraValor->getListadavezregravalor() == $listadavezregravalor) {
                        $listadavezregravalorObj = $itemRegraValor;
                        break;
                    }
                }

                $valor = $listadavezregravalorObj->getValor();
                break;
            }

            // Estado
            case self::REGRA_TIPO_UF: {
                $valor = $configuracaoOpcao->getIdestado()->getUf();
                break;
            }

            // Negócio operação
            case self::REGRA_TIPO_OPERACAO: {
                $valor = $configuracaoOpcao->getIdnegociooperacao()->getPropostaOperacao();
                break;
            }

            // Segmento de atuação
            case self::REGRA_TIPO_SEGMENTO: {
                $valor = $configuracaoOpcao->getIdsegmentoatuacao()->getSegmentoatuacao();
                break;
            }
        }

        return $valor;
    }

    /**
     * Monta uma lista de itens da arvore de acordo com o pai e a lista passada
     */
    private function montaArvore($pai, $lista){
        $filhos = [];
        foreach ($lista as $index => $item) {
            // Pego ids do item e do pai de acordo com o item e o pai estar adicionado ou não
            $itemId = $item->getListadavezconfiguracao() != null ? $item->getListadavezconfiguracao() : $item->getListadavezconfiguracaonovo();
            $itemIdPai = $item->getIdpai() != null ? $item->getIdpai() : $item->getIdpainovo();
            
            if ($itemIdPai == $pai) {
                $itemArvoreFilho = [
                    'id' => $itemId,
                    'entity' => $item
                ];
                // Removo o item da lista, pra não ser buscado novamente 
                unset($lista[$index]);

                $itemArvoreFilho['filhos'] = $this->montaArvore($itemArvoreFilho['id'], $lista);

                $filhos[] = $itemArvoreFilho;
            }
        }

        return $filhos;
    }

    /**
     * Chama exclusão do item da árvore e seus filhos, de acordo com a lista de exclusão passada.
     */
    private function excluirEmArvore($itemArvore, $listaRemover, $tenant, $id_grupoempresarial, $logged_user){
        $excluir = in_array($itemArvore['id'], $listaRemover);

        // Chamo exclusao dos filhos. Caso o item deva ser excluído, a exclusao é forçada
        foreach ($itemArvore['filhos'] as $itemArvoreFilho) {
            $this->excluirEmArvore($itemArvoreFilho, $listaRemover, $tenant, $id_grupoempresarial, $logged_user);
        }

        // Excluo item da árvore
        if ($excluir) {
            $this->delete($tenant, $id_grupoempresarial, $itemArvore['entity']);
        }
    }

    /**
     * Chama adição do item da árvore e seus filhos, de acordo com a lista de adição passada.
     */
    private function adicionarEmArvore($itemArvore, $tenant, $id_grupoempresarial, $logged_user, $idPai = null){
        $adicionar = $itemArvore['entity']->getListadavezconfiguracao() == null;
        $guidItemArvore = $itemArvore['id'];

        // Adiciono item da árvore
        if ($adicionar) {
            // Seto o id da configuração pai
            $itemArvore['entity']->setIdpai($idPai);
            // Adiciono item no banco
            $itemArvoreBD = $this->insert($tenant, $id_grupoempresarial, $logged_user, $itemArvore['entity']);
            
            // Seto o guid do item adicionado, para utilizar na adição dos filhos
            $guidItemArvore = $itemArvoreBD['listadavezconfiguracao'];
        }

        // Chamo adição dos filhos, passando o id do pai
        foreach ($itemArvore['filhos'] as $itemArvoreFilho) {
            $this->adicionarEmArvore($itemArvoreFilho, $tenant, $id_grupoempresarial, $logged_user, $guidItemArvore);
        }
    }

    /**
     * Pega o valor do negócio utilizado para validar determinado tipo de regra.
     *  - Tipo 0 (Valor fixo):
     *      + Já é cliente: Se ehcliente == true ? '1' : '0';
     *      + Faturamento Anual: Pega o valor de clientereceitaanual
     *      + Cargo do Contato: Retorna um array com o campo 'cargo' de todos os negocioscontatos
     *  - Tipo 1 (Estado): Pega o valor de uf->uf
     *  - Tipo 2 (Negócio Operação): Pega o valor de operacao->proposta_operacao
     *  - Tipo 3 (Segmento de atuação): Pega o valor de clientesegmentodeatuacao->segmentoatuacao
     */
    private function getNegocioValor($regraObj, \Nasajon\MDABundle\Entity\Crm\Negocios $negocio){
        $valor = null;

        // Busco valor de acordo com o tipo da regra
        switch ($regraObj->getTipoentidade()) {
            // Valor fixo
            case self::REGRA_TIPO_FIXA: {
                // Busco valor de acordo com qual regra do tipo fixo
                switch ($regraObj->getNome()) {
                    case self::REGRA_FIXA_JA_E_CLIENTE: {
                        $valor = ($negocio->getEhcliente()) ? '1' : '0';
                        break;
                    }
                    case self::REGRA_FIXA_FATURAMENTO_ANUAL: {
                        $valor = $negocio->getClientereceitaanual();
                        break;
                    }
                    case self::REGRA_FIXA_CARGO_CONTATO: {
                        $arrContatos = $negocio->getNegocioscontatos();

                        $valor = [];
                        foreach ($arrContatos as $contato) {
                            $valor[] = $contato->getCargo();
                        }
                        break;
                    }
                }
                break;
            }

            // Estado
            case self::REGRA_TIPO_UF: {
                $valor = $negocio->getUf()->getUf();
                break;
            }

            // Negócio operação
            case self::REGRA_TIPO_OPERACAO: {
                $valor = $negocio->getOperacao()->getPropostaOperacao();
                break;
            }

            // Segmento de atuação
            case self::REGRA_TIPO_SEGMENTO: {
                $valor = $negocio->getClientesegmentodeatuacao()->getSegmentoatuacao();
                break;
            }
        }

        return $valor;
    }

    /**
     * Avalia se o negócio é válido para a opção
     */
    private function avaliarNegocioNaOpcao($configuracaoOpcao, \Nasajon\MDABundle\Entity\Crm\Negocios $negocio) {
        // Busco objeto da regra
        $regraObj = $this->findRegra($configuracaoOpcao->getListadavezregra()->getListadavezregra(), $this->tenant, $this->id_grupoempresarial);
        // Pego o valor da opção
        $valorOpcao = $this->getConfiguracaoOpcaoValor($regraObj, $configuracaoOpcao);
        // Pego o valor do negócio
        $valorNegocio = $this->getNegocioValor($regraObj, $negocio);

        // Se for uma regra do tipo fixo e for a regra 'Cargo do contato':
        //  - Verifico se o cargo em $valorOpcao da opção está dentro do array de cargos em $valorNegocio
        if ($regraObj->getTipoentidade() == self::REGRA_TIPO_FIXA && $regraObj->getNome() == self::REGRA_FIXA_CARGO_CONTATO) {
            return in_array($valorOpcao, $valorNegocio);
        } else {
            // Valido se os valores da opção e do negócio são iguais
            return $valorOpcao == $valorNegocio;
        }
    }

    /**
     * Valida o negócio dentro da árvore de regras para buscar a lista de vendedores, ou se é um vendedorfixo.
     * Função recursiva, que chama a si mesma para validar os filhos, aplicando a validação em forma de árvore
     * @return \Nasajon\AppBundle\Entity\Crm\ValidacaoNegocioListaDaVezConfiguracao
     */
    public function validarNegocioNaArvoreDeRegras($tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Negocios $negocio, $itemArvore = null){
        // Seto construtores
        $this->tenant = $tenant;
        $this->id_grupoempresarial = $id_grupoempresarial;

        // Monto objeto de retorno
        $retorno = new ValidacaoNegocioListaDaVezConfiguracao();

        // Se é a primeira chamada
        if ($itemArvore == null) {
            // Busco configurações
            $arrConfiguracoes = $this->findAll($this->tenant, $this->id_grupoempresarial);
            
            $arrConfiguracoesObj = [];
            for ($i=0; $i < count($arrConfiguracoes) ; $i++) { 
                $entityArr = $arrConfiguracoes[$i];
                $obj = $this->fillEntity($entityArr);

                $arrConfiguracoesObj[] = $obj;
            }
            // Monto árvore
            $arrArvore = $this->montaArvore(null, $arrConfiguracoesObj);

            // Se não possui configuração, seto que não possui configuração no retorno e saio da função
            if (count($arrArvore) == 0) {
                $retorno->setPossuiconfiguracao(false);
                return $retorno;
            }

            // Seto objeto de árvore
            $itemArvore = $arrArvore[0];
        }

        // Pego a configuração do item da árvore
        $configuracao = $itemArvore['entity'];

        // Pego filhos do item da árvore
        $arrItemArvoreFilhos = $itemArvore['filhos'];

        // Se configuração é do tipo Regra
        if ($configuracao->getTiporegistro() == self::CONFIGURACAO_TIPO_REGRA){
            // Índice para buscar e validar o fluxo alternativo
            $indexFluxoAlternativo = -1;

            // Ordeno lista de filhos pelo campo ordem
            usort($arrItemArvoreFilhos, function ($item1, $item2) {
                //Se o ITEM 1 tiver ordem menor que o ITEM 2, deixo o ITEM 1 antes
                if ($item1['entity']->getOrdem() < $item2['entity']->getOrdem()) {
                    return -1;
                }
                //Se o ITEM 1 tiver ordem maior que o ITEM 2, deixo o ITEM 2 antes
                else if ($item1['entity']->getOrdem() > $item2['entity']->getOrdem()) {
                    return 1;
                } else {
                    return 0; //Não faz diferença a ordem.
                }
            });

            // Percorro os filhos para aplicar a avaliação nas opções até algum retornar válido
            for ($i=0; $i < count($arrItemArvoreFilhos); $i++) { 
                // Pego o filho
                $itemArvoreFilho = $arrItemArvoreFilhos[$i];
                $configuracaoFilho = $itemArvoreFilho['entity'];

                // Se for fluxo alternativo, pulo para o próximo registro
                if ($configuracaoFilho->getTiporegistro() == self::CONFIGURACAO_TIPO_FLUXO_ALTERNATIVO) {
                    $indexFluxoAlternativo = $i;
                    continue;
                }

                // Chamo validação para o filho
                $validacaoFilho = $this->validarNegocioNaArvoreDeRegras($this->tenant, $this->id_grupoempresarial, $negocio, $itemArvoreFilho);

                // Verifico se o negócio foi validado na opção
                if ($validacaoFilho->getValido()){
                    $retorno = $validacaoFilho;
                    break;
                }
            }

            // Se o retorno não for válido, pego a lista da vez do fluxo alternativo
            if (!$retorno->getValido() && $indexFluxoAlternativo > -1) {
                // Chamo validação para o filho
                $itemArvoreFluxoAlternativo = $arrItemArvoreFilhos[$indexFluxoAlternativo];
                $configuracaoFluxoAlternativo = $itemArvoreFluxoAlternativo['entity'];
                $retorno = $this->validarNegocioNaArvoreDeRegras($this->tenant, $this->id_grupoempresarial, $negocio, $itemArvoreFluxoAlternativo);
            }
        } 
        // Se a configuração é tipo Opção ou Fluxo alternativo
        else {
            // Seto que a opção é válida por padrão, caso seja fluxo alternativo
            $opcaoValidada = true;
            
            // Se a configuração for do tipo opção verifico se a opção é válida
            if ($configuracao->getTiporegistro() == self::CONFIGURACAO_TIPO_OPCAO) {
                $opcaoValidada = $this->avaliarNegocioNaOpcao($configuracao, $negocio);
            }
            
            // Se a opção for válida
            if ($opcaoValidada) {
                // Se não tem filhos, já retorno que o negócio foi validado
                if (count($arrItemArvoreFilhos) == 0) {
                    // Seto que o retorno é válido
                    $retorno->setValido(true);
                    // Seto se é vendedor fixo
                    $retorno->setVendedorfixo($configuracao->getVendedorfixo());
                    // Seto a lista de vendedores
                    $retorno->setListadavezvendedor($configuracao->getListadavezvendedor());
                } 
                // Senão: Chamo validação em árvore para o filho regra
                else {
                    $retorno = $this->validarNegocioNaArvoreDeRegras($this->tenant, $this->id_grupoempresarial, $negocio, $arrItemArvoreFilhos[0]);
                }
            }
        }

        return $retorno;
    }
}