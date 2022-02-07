<?php

namespace AppBundle\Service\Web;

use Nasajon\MDABundle\Request\Filter;
use AppBundle\Enum\ConfiguracoesGeraisConstant;
use Nasajon\MDABundle\Request\FilterExpression;
use AppBundle\Resources\Constant\ConfiguracoesConstant;
use Nasajon\MDABundle\Repository\Web\ConfiguracoesRepository;
use AppBundle\Service\Ns\ConfiguracoesService as NsConfiguracoesService;
use Nasajon\MDABundle\Service\Web\ConfiguracoesService as ParentService;

class ConfiguracoesService extends ParentService
{   
    /**
     * @var ConfiguracoesRepository
     */
    protected $repository;

    /**
     * @var NsConfiguracoesService
     */
    protected $nsconfiguracoesService;

    public function __construct(
        ConfiguracoesRepository $repository,
        NsConfiguracoesService $nsconfiguracoesService
    )
    {
        $this->repository = $repository;
        $this->nsconfiguracoesService = $nsconfiguracoesService;  
    }

    /**
     * @return array
     */
    public function findAll($tenant, Filter $filter = null)
    {
        $configuracoes = [];

        $this->getRepository()->validateOffset($filter);
        $entities = $this->getRepository()->findAll($tenant, $filter);

        foreach ($entities as $configuracao) {
            $configuracoes[$configuracao['chave']] = $configuracao;

            if (in_array($configuracao['chave'], ConfiguracoesConstant::CONFIGURACOES_BOOLEANAS)) {
                $configuracoes[$configuracao['chave']]['valor'] = $configuracao['valor'] === "true" ? true : false;
            }
        }

        return $configuracoes;   
    }

    /**
     * @return bool
     */
    public function getConfiguracaoDocumentosObrigatorios(int $tenant)
    {
        $filter = new Filter();
        $filter->addToFilterExpression(new FilterExpression('sistema', 'eq', 'PERSONACLIENTE'));
        $filter->addToFilterExpression(new FilterExpression('chave', 'eq', 'CONFIGURACAO_DOCS_OBRIGATORIOS'));

        $response = $this->getRepository()->findAll($tenant, $filter);

        return (!count($response)) ? false : $response[0]['valor'];
    }

    /**
     * Devolve o valor da chave nas configurações
     * @todo verificar o filter no construto e substituir em negócio
     * @return array|null
     */
    public function getValor(int $tenant, string $chave)
    {
        $filter = new Filter();
        $filterExpression = $filter->getFilterExpression();

        array_push($filterExpression, new FilterExpression('chave', 'eq', $chave));
        array_push($filterExpression, new FilterExpression('sistema', 'eq', 'MEUTRABALHO'));

        $filter->setFilterExpression($filterExpression);

        $conf = $this->findAll($tenant, $filter);

        if (sizeof($conf) > 0) {
            return $conf[$chave]['valor'];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getConfiguracoesFormatadas(int $tenant)
    {
        $filter = new Filter();
        $filter->addToFilterExpression(new FilterExpression('sistema', 'eq', 'PERSONACLIENTE'));
        $filter->addToFilterExpression(new FilterExpression('sistema', 'eq', 'MEUTRABALHO'));

        $configs = $this->findAll($tenant, $filter);

        $response = [];
        $response['TIMEZONE'] = isset($configs['TIMEZONE']) ? $configs['TIMEZONE']['valor'] : 'America/Sao_Paulo';

        $response['SOLICITACAO_FERIAS_VENDER_DIAS'] = $configs['SOLICITACAO_FERIAS_VENDER_DIAS'];
        $response['SOLICITACAO_FERIAS_ADT_13'] = $configs['SOLICITACAO_FERIAS_ADT_13'];
        $response['NOTIFICACAO_GESTORES_TODOS_NIVEIS'] = isset($configs['NOTIFICACAO_GESTORES_TODOS_NIVEIS']) ? $configs['NOTIFICACAO_GESTORES_TODOS_NIVEIS']['valor'] : false;

        $response['NOT_MEUTRABALHO_CANCELAR_ALTERACAO_ENDERECO'] =  isset($configs['NOT_MEUTRABALHO_CANCELAR_ALTERACAO_ENDERECO']) ? $configs['NOT_MEUTRABALHO_CANCELAR_ALTERACAO_ENDERECO']['valor'] : false;

        $response['NOT_MEUTRABALHO_CANCELAR_ALTERACAO_VT'] =  isset($configs['NOT_MEUTRABALHO_CANCELAR_ALTERACAO_VT']) ? $configs['NOT_MEUTRABALHO_CANCELAR_ALTERACAO_VT']['valor'] : false;

        $response['NOT_MEUTRABALHO_CANCELAR_FALTA'] =  isset($configs['NOT_MEUTRABALHO_CANCELAR_FALTA']) ? $configs['NOT_MEUTRABALHO_CANCELAR_FALTA']['valor'] : false;

        $response['NOT_MEUTRABALHO_CANCELAR_FERIAS'] =  isset($configs['NOT_MEUTRABALHO_CANCELAR_FERIAS']) ? $configs['NOT_MEUTRABALHO_CANCELAR_FERIAS']['valor'] : false;

        $response['TEM_MEUTRABALHO_CANCELAR_ALTERACAO_ENDERECO'] =  isset($configs['TEM_MEUTRABALHO_CANCELAR_ALTERACAO_ENDERECO']) ? $configs['TEM_MEUTRABALHO_CANCELAR_ALTERACAO_ENDERECO']['valor'] : 'meurh_solicitacao_generico';

        $response['TEM_MEUTRABALHO_CANCELAR_ALTERACAO_VT'] =  isset($configs['TEM_MEUTRABALHO_CANCELAR_ALTERACAO_VT']) ? $configs['TEM_MEUTRABALHO_CANCELAR_ALTERACAO_VT']['valor'] : 'meurh_solicitacao_generico';

        $response['TEM_MEUTRABALHO_CANCELAR_FALTA'] =  isset($configs['TEM_MEUTRABALHO_CANCELAR_FALTA']) ? $configs['TEM_MEUTRABALHO_CANCELAR_FALTA']['valor'] : 'meurh_solicitacao_generico';

        $response['TEM_MEUTRABALHO_CANCELAR_FERIAS'] =  isset($configs['TEM_MEUTRABALHO_CANCELAR_FERIAS']) ? $configs['TEM_MEUTRABALHO_CANCELAR_FERIAS']['valor'] : 'meurh_solicitacao_generico';

        return $response;
    }

    /**
     * @return array
     */
    public function getConfiguracoesFormatadasPorEstabelecimento(int $tenant, ?string $estabelecimento)
    {
        $filter = new Filter();
        $filter->addToFilterExpression(new FilterExpression('sistema', 'eq', 'PERSONACLIENTE'));
        $filter->addToFilterExpression(new FilterExpression('sistema', 'eq', 'MEUTRABALHO'));

        $configs = $this->findAll($tenant, $filter);

        $response = [];
        $response['TIMEZONE'] = isset($configs['TIMEZONE']) ? $configs['TIMEZONE']['valor'] : 'America/Sao_Paulo';

        $response['SOLICITACAO_FERIAS_VENDER_DIAS'] = $configs['SOLICITACAO_FERIAS_VENDER_DIAS']['valor'];
        $response['SOLICITACAO_FERIAS_ADT_13'] = $configs['SOLICITACAO_FERIAS_ADT_13']['valor'];
        $response['NOTIFICACAO_GESTORES_TODOS_NIVEIS'] = isset($configs['NOTIFICACAO_GESTORES_TODOS_NIVEIS']) ? $configs['NOTIFICACAO_GESTORES_TODOS_NIVEIS']['valor'] == 'true': false;

        $response['NOT_MEUTRABALHO_CANCELAR_ALTERACAO_ENDERECO'] =  isset($configs['NOT_MEUTRABALHO_CANCELAR_ALTERACAO_ENDERECO']) ? $configs['NOT_MEUTRABALHO_CANCELAR_ALTERACAO_ENDERECO']['valor'] == 'true': false;

        $response['NOT_MEUTRABALHO_CANCELAR_ALTERACAO_VT'] =  isset($configs['NOT_MEUTRABALHO_CANCELAR_ALTERACAO_VT']) ? $configs['NOT_MEUTRABALHO_CANCELAR_ALTERACAO_VT']['valor'] == 'true': false;

        $response['NOT_MEUTRABALHO_CANCELAR_FALTA'] =  isset($configs['NOT_MEUTRABALHO_CANCELAR_FALTA']) ? $configs['NOT_MEUTRABALHO_CANCELAR_FALTA']['valor'] == 'true': false;

        $response['NOT_MEUTRABALHO_CANCELAR_FERIAS'] =  isset($configs['NOT_MEUTRABALHO_CANCELAR_FERIAS']) ? $configs['NOT_MEUTRABALHO_CANCELAR_FERIAS']['valor'] == 'true': false;

        $response['TEM_MEUTRABALHO_CANCELAR_ALTERACAO_ENDERECO'] =  isset($configs['TEM_MEUTRABALHO_CANCELAR_ALTERACAO_ENDERECO']) ? $configs['TEM_MEUTRABALHO_CANCELAR_ALTERACAO_ENDERECO']['valor'] : 'meurh_solicitacao_generico';

        $response['TEM_MEUTRABALHO_CANCELAR_ALTERACAO_VT'] =  isset($configs['TEM_MEUTRABALHO_CANCELAR_ALTERACAO_VT']) ? $configs['TEM_MEUTRABALHO_CANCELAR_ALTERACAO_VT']['valor'] : 'meurh_solicitacao_generico';

        $response['TEM_MEUTRABALHO_CANCELAR_FALTA'] =  isset($configs['TEM_MEUTRABALHO_CANCELAR_FALTA']) ? $configs['TEM_MEUTRABALHO_CANCELAR_FALTA']['valor'] : 'meurh_solicitacao_generico';

        $response['TEM_MEUTRABALHO_CANCELAR_FERIAS'] =  isset($configs['TEM_MEUTRABALHO_CANCELAR_FERIAS']) ? $configs['TEM_MEUTRABALHO_CANCELAR_FERIAS']['valor'] : 'meurh_solicitacao_generico';

        
        $config = $this->nsconfiguracoesService->getConfiguracaoByCampo(ConfiguracoesGeraisConstant::CAMPOSGERAIS_MARCAR_FERIAS_ANTES_PERIODO, $tenant, $estabelecimento);
        $response['MARCAR_FERIAS_ANTES_PERIODO'] = sizeof($config) > 0 ? ($config[0]['valor'] == '-1' ? true : false) : false;
        return $response;
    }
}