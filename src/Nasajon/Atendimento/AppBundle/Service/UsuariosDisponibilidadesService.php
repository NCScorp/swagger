<?php

namespace Nasajon\Atendimento\AppBundle\Service;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes;
use Nasajon\Atendimento\AppBundle\Repository\Admin\SolicitacoesRepository;
use Nasajon\Atendimento\AppBundle\Repository\Admin\UsuariosDisponibilidadesRepository;

class UsuariosDisponibilidadesService {

    /**
     *
     * @var UsuariosDisponibilidadesRepository
     */
    protected $udRepo;

    /**
     *
     * @var SolicitacoesRepository
     */
    private $atendimentosRepository;

    /**
     * @var Producer
     */
    private $chamadosRegrasProducer;

    /**
     *
     * @param UsuariosDisponibilidadesRepository $udRepo
     */
    public function __construct(
        UsuariosDisponibilidadesRepository $udRepo, 
        SolicitacoesRepository $atendimentosRepository,
        Producer $chamadosRegrasProducer
    ) {
        $this->udRepo = $udRepo;
        $this->atendimentosRepository = $atendimentosRepository;
        $this->chamadosRegrasProducer = $chamadosRegrasProducer;
    }

    /**
     * Quando o Usuário está Indisponível, o que fazer com os chamados atribuídos a ele
     * @param array $acao_configurada
     * @return boolean
     */
    public function atribuicaoDosChamadosDeUsuarioIndisponibilizado($acao_configurada, $atendimento) {

        $atendimento = $this->atendimentosRepository->fillEntity($atendimento);

        switch ($acao_configurada) {
            case 0:
                return $atendimento;
            case 1:
                return $this->deixarSemAtribuicao($atendimento);
            case 2:
                return $this->reatribuirParaFilaOuManterAtribuicao($atendimento);
            case 3:
                return $this->reatribuirParaFilaOuDeixarSemAtribuicao($atendimento);
        }

    }

    /**
     * Deixar sem atribuição
     *
     * @param array $atendimento
     * @return \Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes
     */
    protected function deixarSemAtribuicao($atendimento) {
        $atendimento->setResponsavelWeb(null);
        $atendimento->setResponsavelWebTipo(0);
        $this->atendimentosRepository->alterarResponsavelWeb([], $atendimento->getTenant(), $atendimento);
        return $atendimento;
    }

    /**
     * Reatribrir para a última Fila em que esteve. Caso esta não exista, manter atribuição
     *
     * @param array $atendimento
     * @return \Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes
     */
    protected function reatribuirParaFilaOuManterAtribuicao($atendimento) {
        $atendimentofila = $this->atendimentosRepository->getUltimaFilaAtribuida($atendimento);
        if ($atendimentofila) {
            $atendimento->setResponsavelWeb($atendimentofila);
            $atendimento->setResponsavelWebTipo(2);
            $this->atendimentosRepository->alterarResponsavelWeb([], $atendimento->getTenant(), $atendimento);
        }
        return $atendimento;
    }

    /**
     * Reatribrir para a última Fila em que esteve. Caso esta não exista, deixar sem atribuição
     *
     * @param array $atendimento
     * @return \Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes
     */
    public function reatribuirParaFilaOuDeixarSemAtribuicao($atendimento) {
        $atendimentofila = $this->atendimentosRepository->getUltimaFilaAtribuida($atendimento);
        if ($atendimentofila) {
            $atendimento->setResponsavelWeb($atendimentofila);
            $atendimento->setResponsavelWebTipo(2);
            $this->atendimentosRepository->alterarResponsavelWeb([], $atendimento->getTenant(), $atendimento);
        } else {
            $atendimento = $this->deixarSemAtribuicao($atendimento);
        }
        return $atendimento;
    }

    /**
     * Reatribrir para a última Fila em que esteve. Caso esta não exista, direcionar à Regras 
     * @param Solicitacoes $atendimento
     * @return void
     */
    public function direcionarParaRegras(Solicitacoes $atendimento) {
        $atendimentofila = $this->atendimentosRepository->getUltimaFilaAtribuida($atendimento);
    
        if ($atendimentofila) {
            $atendimento->setResponsavelWeb($atendimentofila);
            $atendimento->setResponsavelWebTipo(2);
            $this->atendimentosRepository->alterarResponsavelWeb([], $atendimento->getTenant(), $atendimento);
        } else {
            $this->chamadosRegrasProducer->publish(
                json_encode([
                    'tenant'=> $atendimento->getTenant(), 
                    'atendimento'=> $atendimento->getAtendimento()
                ])
            );
        }
    }

    /**
     * Verifica se usuário está indisponível
     *
     * @param mixed $tenant
     * @param mixed $usuario
     * @return boolean
     */
    public function verificarSeUsuarioIndisponivel($usuario, $tenant) {
        return $this->udRepo->verificarSeUsuarioIndisponivel($usuario, $tenant);
    }

}
