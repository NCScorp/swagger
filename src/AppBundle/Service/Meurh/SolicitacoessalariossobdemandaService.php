<?php

namespace AppBundle\Service\Meurh;

use Doctrine\ORM\NoResultException;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Service\Financas\AgenciasService;
use Nasajon\MDABundle\Service\Web\ConfiguracoesService;
use Nasajon\MDABundle\Service\Persona\TrabalhadoresService;
use Doctrine\Instantiator\Exception\UnexpectedValueException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Enum\Ns\configuracoesGeraisEnum as ConfiguracoesGeraisEnum;
use Nasajon\MDABundle\Service\Meurh\ValoressolicitacoesporperiodoService;
use Nasajon\MDABundle\Service\Meurh\SolicitacoessalariossobdemandaService as ServiceParent;

/**
 * SolicitacoessalariossobdemandaService
 *
 */
class SolicitacoessalariossobdemandaService extends ServiceParent
{
    public $agenciasService;
    public $trabalhadoresService;
    public $configuracoesService;
    public $valoressolicitacoesporperiodoService;
    public $fixedAttributes;

    public function __construct(\Nasajon\MDABundle\Repository\Meurh\SolicitacoessalariossobdemandaRepository $repository, AgenciasService $agenciasService, TrabalhadoresService $trabalhadoresService, ConfiguracoesService $configuracoesService, ValoressolicitacoesporperiodoService $valoressolicitacoesporperiodoService, $fixedAttributes)
    {
        parent::__construct($repository);
        $this->agenciasService = $agenciasService;
        $this->trabalhadoresService = $trabalhadoresService;
        $this->configuracoesService = $configuracoesService;
        $this->valoressolicitacoesporperiodoService = $valoressolicitacoesporperiodoService;
        $this->fixedAttributes = $fixedAttributes;
    }

    /**
     * @return array
     * Sobrescrito para buscar agência do trabalhador, join não suportado pelo MDA.
     */
    public function findAll($tenant, $trabalhador, Filter $filter = null){
        $solicitacoes = parent::findAll($tenant, $trabalhador, $filter);
        $trabalhadorEntity = $this->trabalhadoresService->find($trabalhador, $tenant);
        try {
            $trabalhadorEntity["agencia"] = ($this->agenciasService->find($trabalhadorEntity["agencia"]["agencia"], $tenant));
        } catch (NoResultException $e) {
            throw new NotFoundHttpException("Agência não encontrada.");
        }

        // Array map de solicitacões. Em cada uma delas, aciona o service de agências fazendo um this agenciasService->find(agencia, tenant) (o guid e o tenant) e o retorna.

        $solicitacoes = array_map(function ($solicitacao) use ($trabalhadorEntity) {
            $solicitacao["trabalhador"] = $trabalhadorEntity;
            return $solicitacao;
        }, $solicitacoes);

        return $solicitacoes;
    }

    /**
     * @param string  $trabalhador
     * @param string  $logged_user
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Meurh\Solicitacoessalariossobdemanda $entity
     * @return string
     * @throws \Exception
     */
    public function insert($trabalhador, $logged_user, $tenant, \Nasajon\MDABundle\Entity\Meurh\Solicitacoessalariossobdemanda $entity)
    {
        try {
            $this->getRepository()->begin();

            $entity->setEstabelecimento($entity->getTrabalhador()->getEstabelecimento());

            $filter = new Filter();
            $filterExpression = $filter->getFilterExpression();
            array_push($filterExpression, new FilterExpression('trabalhador', 'eq', $trabalhador));
            array_push($filterExpression, new FilterExpression('ano', 'eq', date("Y")));
            array_push($filterExpression, new FilterExpression('mes', 'eq', date("n")));
            $filter->setFilterExpression($filterExpression);

            $result = $this->valoressolicitacoesporperiodoService->findAll($tenant, $filter);

            $valordisponivel = isset($result[0]) ? $result[0]["valordisponivel"] : 0;
            $valorpendente = isset($result[0]) ? $result[0]["valorpendente"] : 0;
            $valoraprovado = isset($result[0]) ? $result[0]["valoraprovado"] : 0;

            $valormaximo = $valordisponivel - $valorpendente - $valoraprovado;
            $valorminimo = $this->configuracoesService->getValor($tenant, ConfiguracoesGeraisEnum::SOLIC_SAL_SOBDEMANDA_MIN);
            $provedorSolicitacao = json_decode($this->configuracoesService->getValor($tenant, configuracoesGeraisEnum::SOLIC_SAL_SOBDEMANDA_PROVEDOR));
            $estabelecimentoAtual = $entity->getTrabalhador()->getEstabelecimento();

            $valorSolicitacao = $entity->getValor();

            if($valorSolicitacao < $valorminimo || $valorSolicitacao > $valormaximo) {
                throw new UnexpectedValueException("Valor inválido.");
            }

            if($provedorSolicitacao != null) {
                if(sizeof($provedorSolicitacao->estabelecimentos) == 0 || !in_array($estabelecimentoAtual, $provedorSolicitacao->estabelecimentos)) {
                    throw new NotFoundHttpException("Provedor financeiro não encontrado.");
                }
            } else {
                throw new NotFoundHttpException("Provedor financeiro não encontrado.");
            }

            $response = $this->getRepository()->insert($trabalhador, $logged_user, $tenant,  $entity);

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

}
