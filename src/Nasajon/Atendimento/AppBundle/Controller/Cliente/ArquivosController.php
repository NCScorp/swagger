<?php
namespace Nasajon\Atendimento\AppBundle\Controller\Cliente;

use Doctrine\ORM\NoResultException;
use Nasajon\MDABundle\Request\Filter;
use Doctrine\DBAL\Exception\DriverException;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as FOS;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Arquivos;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Nasajon\Atendimento\AppBundle\Security\Authorization\Voter\ArquivosVoter;
use Nasajon\MDABundle\Controller\Atendimento\Cliente\ArquivosController as ArquivosParentController;

class ArquivosController extends ArquivosParentController {


    /**
     * Lists all Atendimento\Cliente\Arquivos entities.
     * @FOS\Get("/arquivos/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter")
     */
    public function indexAction(Filter $filter = null, Request $request) {

        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        if(!$this->get('nasajon_mda.fixed_attributes')->has('logged_user')){
            return new JsonResponse(null);
        }
        
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

        $clientes = $this->get('nasajon_mda.atendimento_cliente_usuarios_repository')->getClientesByConta($logged_user['email'], $tenant);
        $configuracoesService = $this->get('modelbundle.service.configuracoes');
        $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');
        $entities = null;
        $permitirDownloadArquivos = true;

        //ARQUIVOS_DOWNLOADS_SUPORTE_ATIVO = 1 - Usuário com cliente bloqueado não visualiza arquivos para download.
        if ($configuracoes['ARQUIVOS_DOWNLOADS_SUPORTE_ATIVO'] == 1) {
            $status_suporte = array_map(function ($value) {
                return $value['status_suporte'] == 'Bloqueado' ? 'Bloqueado' : 'Ativo';
            }, $clientes);

            //Conta no array status_suporte quantos estão com suporte bloqueado e quantos estão com suporte ativo.
            $count = array_count_values($status_suporte);

            $permitirDownloadArquivos = empty($count['Ativo']) ? false : true;
        }

        if ($permitirDownloadArquivos) {
            $entity = new Arquivos();
            $this->denyAccessUnlessGranted(ArquivosVoter::INDEX, $entity);
            $entities = $this->getRepository()->findAll($tenant, $clientes, $filter);

            if (!empty($entities)) {
                $entities = array_map(function($dado) {
                    $strDescricaoStripped = strip_tags($dado['descricao']);
                    if (strlen($strDescricaoStripped) > 100) {
                        $dado['resumo'] = preg_replace("/^(.{1,100})(\s.*|$)/s", '\\1...', $strDescricaoStripped);
                    } else {
                        $dado['resumo'] = $strDescricaoStripped;
                    }
                    return $dado;
                }, $entities);
            }
        }
        return new JsonResponse($entities);
    }

    /**
     * @FOS\Get("/arquivos/download/{id}")
     */
    public function downloadAction($id) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $arquivo = $this->getRepository()->find($tenant, $id);

            $url = $arquivo['caminho'];

            // header('Content-Type: application/octet-stream');
            // header("Content-Transfer-Encoding: Binary");
            // header("Content-disposition: attachment; filename=\"" . $arquivo['nomearquivo'] . "\"");
            // readfile($url);

            return $this->redirect($url);
        } catch (NoResultException $arquivo) {
            throw $this->createNotFoundException();
        } catch (DriverException $e) {
            throw $this->createNotFoundException();
        } catch (AccessDeniedException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());
            throw $this->createNotFoundException();
        }
    }
}