<?php
namespace Nasajon\Atendimento\AppBundle\Controller\Cliente;

use FOS\RestBundle\Controller\Annotations as FOS;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ContasController extends Controller 
{
    /**
     * @FOS\Get("/" , defaults={ "_format" = "json" })     
     */
    public function indexAction(Request $request) 
    {
        $tenant = $request->get('tenant');
        $contas = $this->get('nasajon_sdk.diretorio')->getContas($tenant);

        /**
         * Primeiro é vereficado se existem contas
         * Caso existam, é feito um loop nas contas para verificar se existe sobrenome na conta passada no loop
         * Caso exista, o sobrenome é concatenado ao nome
         */
        if ($contas) {
            foreach ($contas as &$conta) {
                $conta['nome'] = (!is_null($conta['sobrenome'])) ? $conta['nome'] . ' ' . $conta['sobrenome'] : $conta['nome'];
            }
        }

        return new JsonResponse($contas, JsonResponse::HTTP_OK);
    }
}
