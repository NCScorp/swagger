<?php

namespace AppBundle\Service\Persona;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Nasajon\MDABundle\Service\Persona\DocumentoscolaboradoresService as ParentService;
use AppBundle\Service\Persona\TrabalhadoresService;
use AppBundle\Enum\EnumMimeExtension;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;

/**
* DocumentoscolaboradoresService
*
*/
class DocumentoscolaboradoresService extends ParentService
{
    /**
     * @var \AppBundle\Repository\Persona\DocumentoscolaboradoresRepository
     */
    protected $repository;

    /**
     * @var \Nasajon\MDABundle\Service\Meurh\SolicitacoesalteracoesenderecosService $solicitacoesalteracoesenderecosService
     */
    protected $solicitacoesalteracoesenderecosService;

    /**
     * @var \AppBundle\Service\Persona\TrabalhadoresService $trabalhadoresService
     */
    protected $trabalhadoresService;

    /**
     * @var ParamBag $fixedAttributes 
     */
    protected $fixedAttributes;

    public function __construct(\AppBundle\Repository\Persona\DocumentoscolaboradoresRepository $repository, 
        \Nasajon\MDABundle\Service\Meurh\SolicitacoesalteracoesenderecosService $solicitacoesalteracoesenderecosService, 
        TrabalhadoresService $trabalhadoresService, 
        ParameterBag $fixedAttributes)
    {
        $this->repository = $repository;
        $this->solicitacoesalteracoesenderecosService = $solicitacoesalteracoesenderecosService;
        $this->trabalhadoresService = $trabalhadoresService;
        $this->fixedAttributes = $fixedAttributes;
    }

    /**
     * Compara se todos os campos são identicos
     */
    private function verificarDados($objeto1, $objeto2){
        if ($objeto1['logradouro'] === $objeto2['logradouro'] &&
            $objeto1['numero'] === $objeto2['numero'] &&
            $objeto1['complemento'] === $objeto2['complemento'] &&
            $objeto1['cep'] === $objeto2['cep'] &&
            $objeto1['bairro'] === $objeto2['bairro'] &&
            $objeto1['tipologradouro']['tipologradouro'] === $objeto2['tipologradouro'] &&
            $objeto1['municipioresidencia']['ibge'] === $objeto2['municipioresidencia'] &&
            $objeto1['paisresidencia']['pais'] === $objeto2['paisresidencia'])
        {
            return true;
        }
    }

    /**Regra: 
     *  Retorar na solicitação mais recente que contenha documentos
     *  Comparar o endereço da solicitação com o endereço atual do colaborador
     *  Se forem identicos, devo retorar o GUID do documento do colaborador
     */
    public function findDocumentosEndereco($tenant){
        $trabalhador = $this->fixedAttributes->get('trabalhador');

        //Buscar a solicitação mais recente que possui um documento vinculado
        $solicitacaoMaisRecente = $this->repository->findSolicitacaoMaisRecente($tenant, $trabalhador);
        //Se retornou a solicitação
        if($solicitacaoMaisRecente){
            //Buscar os dados da solicitação
            $solicitacao = $this->solicitacoesalteracoesenderecosService->find($solicitacaoMaisRecente['solicitacao'], $tenant, $trabalhador);
            //Retornar o endereço atual
            $endereco = $this->trabalhadoresService->enderecocontato($tenant);
            //Comparar o endereço atual com o endereço da solicitação
            if($this->verificarDados($solicitacao, $endereco)){
                // Retornar todos os documentos vinculados a solicitação
                $filter = new Filter();
                $filter->addToFilterExpression(new FilterExpression('solicitacao', 'in', $solicitacaoMaisRecente['solicitacao']));
                $entities = $this->findAll($tenant,$trabalhador,$filter);
                foreach ($entities as $key => $entity) {

                    $extensao = explode('.', $entity['urldocumento']);
                    $entities[$key]['extensao'] = $extensao[count($extensao) - 1];
                    $entities[$key]['mime'] = EnumMimeExtension::extensionToMime[$entities[$key]['extensao']][0];
                }

                return $entities;
            }
        }

        return [];
    }


    /**
     * Retorna o documento do colaborador
     */
    public function findbyDocument($id , $tenant, $trabalhador) {

        $document = $this->repository->findbyDocument($id, $tenant, $trabalhador);

        if(count($document) === 2) {
            $extensao = explode('.', $document['urldocumento']);
            $document['extensao'] = $extensao[count($extensao) - 1];
            $document['mime'] = EnumMimeExtension::extensionToMime[$document['extensao']][0];

            return $document;
        } else {
            throw new NotFoundHttpException();
        }
    }
}