<?php
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use AppBundle\Service\Web\ConfiguracoesService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use AppBundle\Service\Meurh\SolicitacoesferiasService;
use AppBundle\Validator\Constraints\Saldo;



use DateInterval;
use DateTime;

class SaldoValidator extends ConstraintValidator
{
    protected $configuracoesService;

    protected $fixedAttributes;

    protected $solicitacoesferiasService;

    public function __construct(ConfiguracoesService $configuracoesService, ParameterBag $fixedAttributes, SolicitacoesferiasService $solicitacoesferiasService) {
        $this->configuracoesService = $configuracoesService;
        $this->fixedAttributes = $fixedAttributes;
        $this->solicitacoesferiasService = $solicitacoesferiasService;


    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Saldo) {
            throw new UnexpectedTypeException($constraint, Saldo::class);
        }
        if (empty($value)) {
            return;
        }
        $solicitacaoObj = $this->context->getRoot()->getData();
        $tenant = $this->fixedAttributes->get('tenant');
        $trabalhador = $solicitacaoObj->getTrabalhador();
        $solicitacaoguid =   $solicitacaoObj->getSolicitacao(); 
        $datainicioperiodoAquisitivo = $solicitacaoObj->getDatainicioperiodoaquisitivo();
        $solicitacoesAgrupadas = $this->solicitacoesferiasService->findSolicitacoesAgrupadasPorPeriodo($tenant,$trabalhador);
        $periodosAquisitivos = $this->solicitacoesferiasService->listaPeriodosAquisitivosAbertos($tenant,$trabalhador);
        $diasDireito = $this->solicitacoesferiasService->getDireito($this->solicitacoesferiasService->getDiasDireitoPeriodoAquisitivo($tenant,  $trabalhador, $datainicioperiodoAquisitivo));
        $diasVendidos =  $solicitacaoObj->getDiasvendidos(); 
        $estabelecimento = $solicitacaoObj->getEstabelecimento(); 
        foreach($periodosAquisitivos['periodosaquisitivos'] as $key => $periodos) {
            if($periodosAquisitivos['periodosaquisitivos'][$key]['inicioperiodoaquisitivo'] ==  $datainicioperiodoAquisitivo) {
                $diasDireito = $periodosAquisitivos['periodosaquisitivos'][$key]['direito'];
            }
        }
        $solicitacoesPeriodo = [];
        foreach($solicitacoesAgrupadas['periodosaquisitivos'] as $key => $periodos) {
            if($solicitacoesAgrupadas['periodosaquisitivos'][$key]['datainicioperiodoaquisitivo'] ==  $datainicioperiodoAquisitivo) {
                $solicitacoesPeriodo = $solicitacoesAgrupadas['periodosaquisitivos'][$key]['solicitacoes'];
            }
        }

        $saldo = $diasDireito;
        foreach($solicitacoesPeriodo as $key => $solicitacaoAux) {
            #desconsider canceladas, excluídas e recusadas
            if ($solicitacaoAux['solicitacao'] == $solicitacaoguid || $solicitacaoAux['situacao'] >= 2) {
                $saldo = $saldo + $solicitacaoAux['diasferiascoletivas'];
                if (isset($solicitacaoAux['diasvendidos']) && !empty($solicitacaoAux['diasvendidos'])) {
                    $saldo = $saldo + $solicitacaoAux['diasvendidos'];
                }
                unset($solicitacoesPeriodo[$key]);
            } else {
                $saldo = $saldo - $solicitacaoAux['diasferiascoletivas'];
                if (isset($solicitacaoAux['diasvendidos']) && !empty($solicitacaoAux['diasvendidos'])) {
                    $saldo = $saldo - $solicitacaoAux['diasvendidos'];
                }
            }
        }
        $datainiciogozo = $solicitacaoObj->getDatainiciogozo(); 
        $configuracoes = $this->configuracoesService->getConfiguracoesFormatadasPorEstabelecimento($tenant,$estabelecimento);
        $configuracaoMarcarFeriasAntes = $configuracoes['MARCAR_FERIAS_ANTES_PERIODO'];
        if (($datainiciogozo < $datainicioperiodoAquisitivo) && $configuracaoMarcarFeriasAntes && $value != $diasDireito) {
            $this->context->buildViolation("O usuário, ao marcar férias antes do fim do período aquisitivo, deve obrigatoriamente gozar todos os seus dias de direito em um único período")->setParameter('{{ string }}', $value)->addViolation();
        }

        $saldoOriginal = $saldo;
        $saldoAtual = $saldoOriginal - $value;
        if (!empty($diasVendidos)) {
            $saldoAtual = $saldoAtual - $diasVendidos;
        }
        if($saldoAtual < 0) {
            $this->context->buildViolation("Os dias de gozo excedem o saldo disponível para o colaborador")->setParameter('{{ string }}', $value)->addViolation();
        }
        if($value < 5) {
            $this->context->buildViolation("Um período de férias deve ter no mínimo 5 dias")->setParameter('{{ string }}', $value)->addViolation();
        }
        $possuiPeriodocom14dias = false;
        foreach($solicitacoesPeriodo as $solicitacao) {
            if ($solicitacao['diasferiascoletivas'] >= 14 ) {
                $possuiPeriodocom14dias = true;
            }
        }
        if(!$possuiPeriodocom14dias && ($value < 14) && ($saldoAtual < 14)) {
            $this->context->buildViolation("Pelo menos 1 período de férias do colaborador dete ter pelo menos 14 dias")->setParameter('{{ string }}', $value)->addViolation();
        }
    }
}
