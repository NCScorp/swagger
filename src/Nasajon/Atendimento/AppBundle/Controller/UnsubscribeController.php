<?php

namespace Nasajon\Atendimento\AppBundle\Controller;

use Aws\Sns\MessageValidator\Message;
use Aws\Sns\MessageValidator\MessageValidator;
use Exception;
use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Controller\FOSRestController;
use Guzzle\Http\Client;
use Nasajon\Atendimento\AppBundle\Exception\MailRejectException;
use Nasajon\Atendimento\AppBundle\Service\MailReceiverService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class UnsubscribeController extends FOSRestController {


    /**
     * Remoção de usuário como observador de fila
     *
     * @FOS\Get("/fila/{atendimentofilaobservador}", defaults={ "_format" = "html" })
     */
    public function filaAction(Request $request, $atendimentofilaobservador) {

        try {

            $atendimentofilaobservador = $this->get('nasajon_mda.servicos_atendimentosfilasobservadores_repository')->find($atendimentofilaobservador, null, null);
            $atendimentofila = $this->get('nasajon_mda.servicos_atendimentosfilas_repository')->find($atendimentofilaobservador['atendimentofila'], $atendimentofilaobservador['tenant']);

        } catch (Exception $e) {
            $atendimentofilaobservador = null;
            $atendimentofila = null;
        }

        return $this->render('NasajonAtendimentoAppBundle:Templates:unsubscribe-atendimentofilaobservador.html.twig', array(
                'atendimentofilaobservador' => $atendimentofilaobservador,
                'atendimentofila' => $atendimentofila,
                'confirmado' => null
            )
        );
    }

    /**
     * Remoção de usuário como observador de fila
     *
     * @FOS\Get("/fila/{atendimentofilaobservador}/confirmar", defaults={ "_format" = "html" })
     */
    public function filaConfirmarAction(Request $request, $atendimentofilaobservador) {

        try {

            $atendimentofilaobservador = $this->get('nasajon_mda.servicos_atendimentosfilasobservadores_repository')->findObject($atendimentofilaobservador, null, null);
            $this->get('nasajon_mda.servicos_atendimentosfilasobservadores_repository')->delete($atendimentofilaobservador);
            $atendimentofila = $this->get('nasajon_mda.servicos_atendimentosfilas_repository')->find($atendimentofilaobservador->getAtendimentofila(), $atendimentofilaobservador->getTenant());
            $confirmado = true;

        } catch (Exception $e) {
            $atendimentofilaobservador = null;
            $atendimentofila = null;
            $confirmado = null;
        }

        return $this->render('NasajonAtendimentoAppBundle:Templates:unsubscribe-atendimentofilaobservador.html.twig', array(
                'atendimentofilaobservador' => $atendimentofilaobservador,
                'atendimentofila' => $atendimentofila,
                'confirmado' => $confirmado
            )
        );

    }

    /**
     * Remoção de usuário como observador de um chamado
     *
     * @FOS\Get("/chamado/{atendimentoobservador}", defaults={ "_format" = "html" })
     */
    public function chamadoAction(Request $request, $atendimentoobservador) {

        try {

            $atendimentoobservador = $this->get('nasajon_mda.servicos_atendimentosobservadores_repository')->find($atendimentoobservador, null, null);
            $atendimento = $this->get('nasajon_mda.atendimento_admin_solicitacoes_repository')->find($atendimentoobservador['atendimento'], $atendimentoobservador['tenant']);

        } catch (Exception $e) {
            $atendimentoobservador = null;
            $atendimento = null;
        }

        return $this->render('NasajonAtendimentoAppBundle:Templates:unsubscribe-atendimentoobservador.html.twig', array(
                'atendimentoobservador' => $atendimentoobservador,
                'atendimento' => $atendimento,
                'confirmado' => null
            )
        );
    }

    /**
     * Remoção de usuário como observador de um chamado
     *
     * @FOS\Get("/chamado/{atendimentoobservador}/confirmar", defaults={ "_format" = "html" })
     */
    public function chamadoConfirmarAction(Request $request, $atendimentoobservador) {

        try {

            $atendimentoobservador = $this->get('nasajon_mda.servicos_atendimentosobservadores_repository')->findObject($atendimentoobservador, null, null);
            $this->get('nasajon_mda.servicos_atendimentosobservadores_repository')->delete($atendimentoobservador);
            $atendimento = $this->get('nasajon_mda.atendimento_admin_solicitacoes_repository')->find($atendimentoobservador->getAtendimento(), $atendimentoobservador->getTenant());
            $confirmado = true;

        } catch (Exception $e) {
            $atendimentoobservador = null;
            $atendimento = null;
            $confirmado = null;
        }

        return $this->render('NasajonAtendimentoAppBundle:Templates:unsubscribe-atendimentoobservador.html.twig', array(
                'atendimentoobservador' => $atendimentoobservador,
                'atendimento' => $atendimento,
                'confirmado' => $confirmado
            )
        );

    }

}
