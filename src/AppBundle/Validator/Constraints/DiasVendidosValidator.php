<?php
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use AppBundle\Service\Web\ConfiguracoesService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use AppBundle\Service\Meurh\SolicitacoesferiasService;
use AppBundle\Validator\Constraints\DiasVendidos;

class DiasVendidosValidator extends ConstraintValidator
{
    protected $fixedAttributes;

    protected $solicitacoesferiasService;

    public function __construct(ParameterBag $fixedAttributes, SolicitacoesferiasService $solicitacoesferiasService) {
        $this->fixedAttributes = $fixedAttributes;
        $this->solicitacoesferiasService = $solicitacoesferiasService;
    }

    public function validate($value, Constraint $constraint)
    {

        if (!$constraint instanceof DiasVendidos) {
            throw new UnexpectedTypeException($constraint, DiasVendidos::class);
        }

        if (empty($value)) {
            return;
        }
        $solicitacaoObj = $this->context->getRoot()->getData();
        $tenant = $this->fixedAttributes->get('tenant');
        $trabalhador = $solicitacaoObj->getTrabalhador();
        $solicitacaoguid =   $solicitacaoObj->getSolicitacao(); 
        $periodoaquisitivo =   $solicitacaoObj->getDatainicioperiodoaquisitivo(); 
        $periodosAgrupados = $this->solicitacoesferiasService->findSolicitacoesAgrupadasPorPeriodo($tenant,$trabalhador);
        $solicitacoes = [];
        $direito = $this->solicitacoesferiasService->getDireito($this->solicitacoesferiasService->getDiasDireitoPeriodoAquisitivo($tenant,  $trabalhador, $periodoaquisitivo));
        $saldo = $direito;
        foreach($periodosAgrupados['periodosaquisitivos'] as $periodo) {
            if($periodo['datainicioperiodoaquisitivo'] == $periodoaquisitivo){
                $solicitacoes = $periodo['solicitacoes'];
                $saldo = $periodo['saldo'];
                $direito = $periodo['direito'];
                break;
            }
        }

        if (isset($solicitacaoguid) && !empty($solicitacaoguid)) {
            foreach($solicitacoes as $key => $solicitacao) {
                #desconsider canceladas, excluídas e recusadas
                if ($solicitacao['solicitacao'] == $solicitacaoguid || $solicitacao['situacao'] >= 2) {
                    $$saldo = $saldo + $solicitacao['diasferiascoletivas'];
                    if (isset($solicitacao['diasvendidos']) && !empty($solicitacao['diasvendidos'])) {
                        $saldo = $saldo + $solicitacao['diasvendidos'];
                    }
                    unset($solicitacoes[$key]);
                }
            }
        }

        if (!empty($value)) {
            foreach($solicitacoes as $solicitacao) {
                if (!empty($solicitacao['diasvendidos'])) {
                    $this->context->buildViolation("O colaborador só pode vender suas férias em uma única marcação")->setParameter('{{ string }}', $value)->addViolation();
                }
            }
            if (($value > ($direito/3)) || ($value > 10)) {
                $this->context->buildViolation("O colaborador só pode vender até um terço dos dias no qual tem direito a tirar férias naquele período")->setParameter('{{ string }}', $value)->addViolation();
            }
        }


    }
}
