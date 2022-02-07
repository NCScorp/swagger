<?php

namespace AppBundle\Service\Meurh;

use Gaufrette\Adapter;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Meurh\SolicitacoesdocumentosService as ParentService;
use Nasajon\MDABundle\Service\Meurh\SolicitacoesService;
use AppBundle\Service\Ns\EstabelecimentosService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Nasajon\MDABundle\Repository\Meurh\SolicitacoesdocumentosRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Exception\S3Exception;
use AppBundle\Enum\EnumMimeExtension;
use Nasajon\MDABundle\Service\Meurh\SolicitacoesHistoricosService;
use Nasajon\MDABundle\Service\Persona\TiposdocumentoscolaboradoresService;

/**
 * SolicitacoesdocumentosService
 *
 * Sobrescrito para incluir lógica para armazenar os arquivos
 * 
 */
class SolicitacoesdocumentosService extends ParentService
{
    /**
     * @var \Nasajon\MDABundle\Repository\Meurh\SolicitacoesdocumentosRepository
     */
    protected $repository;

    /**
     * 
     * @var Adapter $adapter 
     */
    private $adapter;

    /**
     * 
     * @var SolicitacoesService $solicitacoesService 
     */
    private $solicitacoesService;

    /**
     * 
     * @var EstabelecimentosService $estabelecimentosService 
     */
    private $estabelecimentosService;

    /**
     * 
     * @var ParamBag $fixedAttributes 
     */
    private $fixedAttributes;

    /**
     * 
     * @var SolicitacoesHistoricosService $solicitacoesHistoricosService 
     */
    private $solicitacoesHistoricosService;

    /**
     * 
     * @var TiposdocumentoscolaboradoresService $tiposdocumentoscolaboradoresService 
     */
    private $tiposdocumentoscolaboradoresService;

    public function __construct(SolicitacoesdocumentosRepository $repository, Adapter $adapter, SolicitacoesService $solicitacoesService, EstabelecimentosService $estabelecimentosService, ParameterBag $fixedAttributes, SolicitacoesHistoricosService $solicitacoesHistoricosService, TiposdocumentoscolaboradoresService $tiposdocumentoscolaboradoresService)
    {
        $this->repository = $repository;
        $this->adapter = $adapter;
        $this->fixedAttributes = $fixedAttributes;
        $this->solicitacoesService = $solicitacoesService;
        $this->estabelecimentosService = $estabelecimentosService;
        $this->solicitacoesHistoricosService = $solicitacoesHistoricosService;
        $this->tiposdocumentoscolaboradoresService = $tiposdocumentoscolaboradoresService;
    }

    /**
     * Excluir o arquivo
     * 
     * @param string $id
     */
    public function deleteFile($id) {
        return $this->adapter->delete($id);
    }

    /**
     * Criar o arquivo
     * 
     * @param string $id
     * @param string $content
     */
    public function createFile($id, $content) {
        return $this->adapter->write($id, $content);
    }

    /**
     * @return array
     */
    public function findAllBySolicitacao($solicitacao, $tenant, Filter $filter = null){
        if (empty($filter)) {
            $filter = new Filter();
        }
        $filter->addToFilterExpression(new FilterExpression('solicitacao', 'in', $solicitacao));
        $this->getRepository()->validateOffset($filter);
        $entities = $this->getRepository()->findAll($tenant,  $filter);
        for($i = 0; $i < count($entities); $i++) {
            if(!empty($entities[$i]["caminhodocumento"])){
                $extensao = explode('.', $entities[$i]["caminhodocumento"]);
                $entities[$i]["extensao"] = $extensao[count($extensao) - 1];
                $entities[$i]["mime"] = EnumMimeExtension::extensionToMime[$entities[$i]["extensao"]][0];
            } else {
                $entities[$i]["extensao"] = null;
                $entities[$i]["mime"] = null;
            }
        }
        return $entities;
    }

    /**
     * Retorna documento hospedado na amazon
     * 
     * @param string $id
     * @param string $content
     */
    public function getFileContent($entity, $tenant) {
        $estabelecimento = $this->fixedAttributes->get('estabelecimento');
        $solicitacao = $this->solicitacoesService->find($entity["solicitacao"], $tenant);

        if($solicitacao["estabelecimento"] !== $estabelecimento) {
            throw new AccessDeniedHttpException("Tentativa de acesso a documento não permitido para o seu estabelecimento");
        }

        $report = $this->adapter->read($entity["caminhodocumento"]);
        if($report === false) {
            throw new NotFoundHttpException("Arquivo não encontrado.");
        }

        $response = new Response($report);
        $response->headers->set('Cache-Control', 'must-revalidate');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Content-Description', 'File Transfer');
        $response->headers->set('Content-Disposition', 'attachment;filename="documento.pdf"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Length', strlen($report));
        $response->headers->set('Content-Type', 'application/octet-stream');

        return $response;
    }

    /**
     * Retorna documento hospedado na amazon
     * 
     * @param string $id
     * @param string $content
     */
    public function getFileContentBase64($entity, $tenant) {
        $estabelecimento = $this->fixedAttributes->get('estabelecimento');
        $solicitacao = $this->solicitacoesService->find($entity["solicitacao"], $tenant);

        if($solicitacao["estabelecimento"] !== $estabelecimento) {
            throw new AccessDeniedHttpException("Tentativa de acesso a documento não permitido para o seu estabelecimento");
        }

        $report = $this->adapter->read($entity["caminhodocumento"]);
        if($report === false) {
            throw new NotFoundHttpException("Arquivo não encontrado.");
        }

        $response = base64_encode($report);

        return $response;
    }

    /**
     * @param string  $logged_user
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Meurh\Solicitacoesdocumentos $entity
     * @return string
     * @throws \Exception
     */
    public function insert($logged_user, $tenant, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesdocumentos $entity)
    {
        try {
            $estabelecimento = $this->fixedAttributes->get('estabelecimento');
            $solicitacao = $this->solicitacoesService->find($entity->getSolicitacao(), $tenant);

            if(($solicitacao["situacao"] <= 0) && ($solicitacao["estabelecimento"] === $estabelecimento)) {
                $this->getRepository()->begin();

                $grupoempresarial = $this->estabelecimentosService->findEstabelecimentoComGrupo($tenant, $estabelecimento);

                // Anteriormente nesse método era permitido apenas PDF e não era necessário enviar o base64 com o cabeçalho,
                // esse tratamento serve para permitir que a forma nova e antiga permaneçam funcionando
                if (preg_match('/data:(.*?);base64,/', $entity->getConteudo(), $match) == 1) {
                    $extension = EnumMimeExtension::mimeToExtension[$match[1]];
                    // remove cabeçalho
                    $entity->setConteudo(str_replace($match[0], '', $entity->getConteudo()));
                } else {
                    $extension = "pdf";
                }

                $decoded = base64_decode($entity->getConteudo(), true);

                $response = $this->getRepository()->insert($logged_user, $tenant, $entity);

                // Inserir caminho do arquivo a ser salvo
                $caminho = "{$tenant}/{$grupoempresarial[0]['grupoempresarial']}/meurh/solicitacoesdocumentos/{$estabelecimento}/solicitacoes/{$entity->getSolicitacao()}/{$response['solicitacaodocumento']}.$extension";
                $entity->setCaminhodocumento($caminho);
                $entity->setSolicitacaodocumento($response["solicitacaodocumento"]);

                $response["caminhodocumento"] = $caminho;

                $this->getRepository()->update($tenant, $entity);

                if($this->createFile($entity->getCaminhodocumento(), $decoded)) {
                    $this->getRepository()->commit();
                } else {
                    throw new S3Exception("O documento não pôde ser inserido. Não foi possível escrever na s3!");
                }

                return $response;
            } else {
                throw new AccessDeniedHttpException("O documento não pôde ser inserido com a situação atual da solicitação e/ou estabelecimento escolhido!");
            }
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * Função temporária aguardando fim da refatoração
     * 
     * @param string  $logged_user
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Meurh\Solicitacoesdocumentos $entity
     * @return string
     * @throws \Exception
     */
    public function insertTemp($logged_user, $tenant, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesdocumentos $entity)
    {
        try {
            $estabelecimento = $this->fixedAttributes->get('estabelecimento');
            $solicitacao = $this->solicitacoesService->find($entity->getSolicitacao(), $tenant);

            if(($solicitacao["situacao"] <= 0) && ($solicitacao["estabelecimento"] === $estabelecimento)) {
                $this->getRepository()->begin();

                $grupoempresarial = $this->estabelecimentosService->findEstabelecimentoComGrupo($tenant, $estabelecimento);

                $fileName =  $entity->getConteudo()->getClientOriginalName();
                $extension = substr($fileName, strrpos($fileName, '.') + 1);

                $response = $this->getRepository()->insert($logged_user, $tenant, $entity);

                // Inserir caminho do arquivo a ser salvo
                $caminho = "{$tenant}/{$grupoempresarial[0]['grupoempresarial']}/meurh/solicitacoesdocumentos/{$estabelecimento}/solicitacoes/{$entity->getSolicitacao()}/{$response['solicitacaodocumento']}.$extension";
                $entity->setCaminhodocumento($caminho);
                $entity->setSolicitacaodocumento($response["solicitacaodocumento"]);

                $response["caminhodocumento"] = $caminho;

                if(!is_null($entity->getSolicitacaohistorico())) {
                    $solicitacaoHistorico = $this->solicitacoesHistoricosService->find($entity->getSolicitacaohistorico(), $entity->getTenant());

                    $anexos = json_decode($solicitacaoHistorico["anexos"]);

                    $tipodocumento = $this->tiposdocumentoscolaboradoresService->find($entity->getTipodocumentocolaborador()->getTipodocumentocolaborador(), $entity->getTenant());

                    if(empty($anexos)) {
                        $anexos = new \stdClass();
                        $anexos->anexos = array();
                    }

                    array_push($anexos->anexos, array(
                        "acao" => "0",
                        "nome" => $tipodocumento["descricao"],
                        "caminho" => $caminho
                    ));

                    $entity->setAnexos(json_encode($anexos));
                }

                $this->getRepository()->update($tenant, $entity);

                $binaryData = file_get_contents($entity->getConteudo()->getPathname());

                if($this->createFile($entity->getCaminhodocumento(), $binaryData)) {
                    $this->getRepository()->commit();
                } else {
                    throw new S3Exception("O documento não pôde ser inserido. Não foi possível escrever na s3!");
                }

                return $response;
            } else {
                throw new AccessDeniedHttpException("O documento não pôde ser inserido com a situação atual da solicitação e/ou estabelecimento escolhido!");
            }
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Meurh\Solicitacoesdocumentos $entity
     * @return string
     * @throws \Exception
     */
    public function delete($tenant, \Nasajon\MDABundle\Entity\Meurh\Solicitacoesdocumentos $entity)
    {
        try {
            $estabelecimento = $this->fixedAttributes->get('estabelecimento');
            $solicitacao = $this->solicitacoesService->find($entity->getSolicitacao(), $tenant);

            if(($solicitacao["situacao"] <= 0) && ($solicitacao["estabelecimento"] === $estabelecimento)) {
                $this->getRepository()->begin();

                if(!is_null($entity->getSolicitacaohistorico())) {
                    $solicitacaoDocumento = $this->find($entity->getSolicitacaodocumento(), $entity->getTenant());
                    $solicitacaoHistorico = $this->solicitacoesHistoricosService->find($entity->getSolicitacaohistorico(), $entity->getTenant());

                    $anexos = json_decode($solicitacaoHistorico["anexos"]);

                    $tipodocumento = $this->tiposdocumentoscolaboradoresService->find($entity->getTipodocumentocolaborador()->getTipodocumentocolaborador(), $entity->getTenant());

                    if(empty($anexos)) {
                        $anexos = new \stdClass();
                        $anexos->anexos = array();
                    }

                    array_push($anexos->anexos, array(
                        "acao" => "1",
                        "nome" => $tipodocumento["descricao"]
                    ));

                    $entity->setAnexos(json_encode($anexos));
                }

                if($this->deleteFile($entity->getCaminhodocumento())) {
                    $response = $this->getRepository()->delete($tenant, $entity);
                    $this->getRepository()->commit();
                } else {
                    throw new S3Exception("O documento não pôde ser deletado! Não foi possível remover da s3!");
                }

                return $response;
            } else {
                throw new AccessDeniedHttpException("O documento não pôde ser deletado com a situação atual da solicitação e/ou estabelecimento escolhido!");
            }
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * @param string $id
     * @param mixed $tenant
          
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $tenant){

        $data = $this->getRepository()->find($id, $tenant);
        $extensao = explode('.', $data["caminhodocumento"]);

        $data["extensao"] = $extensao[count($extensao) - 1];
        $data["mime"] = EnumMimeExtension::extensionToMime[$data["extensao"]][0];

        return $data;
    }
}