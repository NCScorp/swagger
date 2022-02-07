<?php

namespace AppBundle\Service\Meurh;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Entity\Web\Configuracoes;
use AppBundle\Enum\DocumentosObrigatoriosEnum;
use Nasajon\MDABundle\Request\FilterExpression;
use AppBundle\Service\Web\ConfiguracoesService;
use Nasajon\MDABundle\Entity\Meurh\Tiposdocumentosrequeridos;
use Nasajon\MDABundle\Entity\Persona\Tiposdocumentoscolaboradores;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Nasajon\MDABundle\Service\Persona\TiposdocumentoscolaboradoresService;
use Nasajon\MDABundle\Repository\Meurh\TiposdocumentosrequeridosRepository;
use Nasajon\MDABundle\Service\Meurh\TiposdocumentosrequeridosService as ParentService;

class TiposdocumentosrequeridosService extends ParentService
{
    /**
     * @var ConfiguracoesService
     */
    protected $configuracoesService;

    /**
     * @var ParameterBag
     */
    protected $fixedAttributes;

    public function __construct(
        TiposdocumentosrequeridosRepository $repository, 
        ConfiguracoesService $configuracoesService,
        TiposdocumentoscolaboradoresService $tiposdocumentoscolaboradoresService, 
        ParameterBag $fixedAttributes
    )
    {
        $this->repository = $repository;
        $this->configuracoesService = $configuracoesService;
        $this->tiposdocumentoscolaboradoresService = $tiposdocumentoscolaboradoresService;
        $this->fixedAttributes = $fixedAttributes;
    }

    /**
     * @return array
     */
    public function findAll($tenant, $tiposolicitacao, Filter $filter = null)
    {
        $this->getRepository()->validateOffset($filter);

        if (!$this->configuracoesService->getConfiguracaoDocumentosObrigatorios($tenant)) {
            $configuracao = new Configuracoes();
            $configuracao->setTenant($tenant);
            $configuracao->setChave("CONFIGURACAO_DOCS_OBRIGATORIOS");
            $configuracao->setValor("true");
            $configuracao->setSistema("PERSONACLIENTE");
            $configuracao->setIdGrupoempresarial(null);
            
            $this->configuracoesService->insert($tenant, $configuracao);

            $estabelecimento = $this->fixedAttributes->get('estabelecimento');
            $logged_user = $this->fixedAttributes->get('logged_user');

            $tiposdocumentosrequeridos = DocumentosObrigatoriosEnum::documentosObrigatorios;
            $tiposdocumentoscolaboradores = $this->tiposdocumentoscolaboradoresService->findAll($tenant);

            for ($i = 0; $i < count($tiposdocumentosrequeridos); $i++) {
                if (count($tiposdocumentoscolaboradores)) {
                    foreach ($tiposdocumentoscolaboradores as $tipodocumento) {
                        if ($tipodocumento['descricao'] == $tiposdocumentosrequeridos[$i]['descricao']) {
                            $tipodocumentorequerido = new Tiposdocumentosrequeridos();
                            $tipodocumentorequerido->setTiposolicitacao($tiposdocumentosrequeridos[$i]["tiposolicitacao"]);
                            $tipodocumentorequerido->setEstabelecimento($estabelecimento);
                            $tipodocumentorequerido->setTenant($tenant);
                            $tipodocumentorequerido->setObrigatorio($tiposdocumentosrequeridos[$i]["obrigatorio"]);
                            $tipodocumentorequerido->setTipodocumentocolaborador(
                                (new Tiposdocumentoscolaboradores())->setTipodocumentocolaborador($tipodocumento["tipodocumentocolaborador"])
                            );

                            $this->getRepository()->insert(
                                $tiposdocumentosrequeridos[$i]["tiposolicitacao"],
                                $tenant,
                                $logged_user,
                                $tipodocumentorequerido
                            );
                        }
                    }
                }
            }
        }

        return $this->getRepository()->findAll($tenant, $tiposolicitacao, $filter);     
    }

    /**
     * @return array
     */
    public function findAllConfiguracoes(int $tenant, string $estabelecimento)
    {
        return $this->getRepository()->findAllConfiguracoes($tenant, $estabelecimento);     
    }
}