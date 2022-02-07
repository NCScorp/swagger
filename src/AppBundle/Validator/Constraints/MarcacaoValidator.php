<?php
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use AppBundle\Validator\Constraints\Marcacao;
use AppBundle\Service\Web\ConfiguracoesService;
use Symfony\Component\Validator\ConstraintValidator;
use AppBundle\Service\Meurh\SolicitacoesferiasService;
use Nasajon\MDABundle\Entity\Meurh\Solicitacoesferias;
use AppBundle\Repository\Meurh\SolicitacoesferiasRepository;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class MarcacaoValidator extends ConstraintValidator
{
    /** 
     * @var ConfiguracoesService
     */
    protected $configuracoesService;

    /**
     * @var ParameterBag
     */
    protected $fixedAttributes;

    /**
     * @var SolicitacoesferiasService
     */
    protected $solicitacoesferiasService;

    /**
     * @var SolicitacoesferiasRepository
     */
    protected $solicitacoesferiasRepository;

    public function __construct(
        ConfiguracoesService $configuracoesService,
        ParameterBag $fixedAttributes,
        SolicitacoesferiasService $solicitacoesferiasService,
        SolicitacoesferiasRepository $solicitacoesferiasRepository
    )
    {
        $this->configuracoesService = $configuracoesService;
        $this->fixedAttributes = $fixedAttributes;
        $this->solicitacoesferiasService = $solicitacoesferiasService;
        $this->solicitacoesferiasRepository = $solicitacoesferiasRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Marcacao) {
            throw new UnexpectedTypeException($constraint, Marcacao::class);
        }

        if (empty($value)) {
            return;
        }

        /** 
         * @var Solicitacoesferias
         */
        $solicitacaoObj = $this->context->getRoot()->getData();
        $solicitacaoguid = $solicitacaoObj->getSolicitacao(); 
        $tenant = $this->fixedAttributes->get('tenant');
        $trabalhador = $solicitacaoObj->getTrabalhador();
        $estabelecimento = $solicitacaoObj->getEstabelecimento(); 
        $datainicioperiodoAquisitivo = $solicitacaoObj->getDatainicioperiodoaquisitivo();

        $this->validaDataInicio($tenant, $value, $solicitacaoObj->getTrabalhador());

        $solicitacoesAgrupadas = $this->solicitacoesferiasService->findSolicitacoesAgrupadasPorPeriodo($tenant,$trabalhador);
        $solicitacoesPeriodo = [];
        foreach ($solicitacoesAgrupadas['periodosaquisitivos'] as $key => $periodos) {
            if ($solicitacoesAgrupadas['periodosaquisitivos'][$key]['datainicioperiodoaquisitivo'] == $datainicioperiodoAquisitivo) {
                $solicitacoesPeriodo = $solicitacoesAgrupadas['periodosaquisitivos'][$key]['solicitacoes'];
            }
        }

        foreach ($solicitacoesPeriodo as $key => $solicitacao) {
            #desconsider canceladas, excluídas e recusadas
            if ($solicitacao['solicitacao'] == $solicitacaoguid || $solicitacao['situacao'] >= 2) { 
                unset($solicitacoesPeriodo[$key]);
            }
        }

        if (count($solicitacoesPeriodo) >= 3) {
            $this->context->buildViolation("Um trabalhador só pode dividir as suas férias em no máximo 3 períodos")->setParameter('{{ string }}', $value)->addViolation();
        }

        $configuracoes = $this->configuracoesService->getConfiguracoesFormatadasPorEstabelecimento($tenant,$estabelecimento);
        $configuracaoMarcarFeriasAntes = $configuracoes['MARCAR_FERIAS_ANTES_PERIODO'];

        if (($value < $datainicioperiodoAquisitivo) && !$configuracaoMarcarFeriasAntes) {
            $this->context->buildViolation("O usuário não pode marcar férias antes do fim do período aquisitivo")->setParameter('{{ string }}', $value)->addViolation();
        }
    }

    /**
     * @param \DateTime|string $dataInicio
     * @return bool
     */
    private function validaDataInicio($tenant, $dataInicio, string $trabalhador)
    {
        $dataInicio = ($dataInicio instanceof \DateTime) ? $dataInicio->format('Y-m-d') : (new \DateTime($dataInicio))->format('Y-m-d');

        $this->verificaDiaSemana($tenant, $dataInicio, $trabalhador);
        $this->verificaFeriados($tenant, $dataInicio);

        return true;
    }

    /**
     * @return bool
     */
    private function verificaDiaSemana(int $tenant, string $dataInicio, string $trabalhador)
    {
        $diaSemana = intval(date('w', strtotime($dataInicio)));

        if ($diaSemana === 0 || $diaSemana === 6) {
            $this->context->buildViolation("Não é possível iniciar as férias no final de semana")->setParameter('{{ string }}', $dataInicio)->addViolation();
            return;
        }

        $repousos = $this->solicitacoesferiasRepository->findRepousosTrabalhador($tenant, $trabalhador);

        if (!count($repousos)) {
            return true;
        }

        $repousos = array_values($repousos[0]);

        foreach ($repousos as $key => $repouso) {
            if (!$repouso) {
                continue;
            }

            $diferencaDias = $key - $diaSemana;

            if (($diferencaDias === 0) || ($diferencaDias === -5) || ($diferencaDias > 0 && $diferencaDias <= 2)) {
                $this->context->buildViolation("'A data inicial das férias deve anteceder pelo menos 2 dias a uma folga'")->setParameter('{{ string }}', $dataInicio)->addViolation();
                return;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function verificaFeriados($tenant, string $dataInicio)
    {
        $feriados = $this->solicitacoesferiasRepository->findHolidays($tenant);
        
        foreach ($feriados as $feriado) {
            $dataInicioObj = new \DateTime($dataInicio);
            $dataFeriadoObj = new \DateTime($feriado['data']);
            $diferencaDias = ($dataInicioObj->diff($dataFeriadoObj))->days;

            if (($diferencaDias === 0) || (($diferencaDias <= 2) && ($dataInicioObj < $dataFeriadoObj))) {
                $this->context->buildViolation("A data inicial das férias deve anteceder pelo menos 2 dias a um feriado")->setParameter('{{ string }}', $dataInicio)->addViolation();
                return;
            }
        }
    }
}
