<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Cliente;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\Atendimento\AppBundle\Entity\HelperGlobals;
use Nasajon\Atendimento\AppBundle\Security\Authorization\Voter\GlobalsVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CategoriasController extends Controller {

    /**
     * Lists all Atendimento\Categorias entities.
     *
     * @FOS\Get("/categorias/", defaults={ "_format" = "json" })     
     */
    public function indexAction(Request $request) {

        if (!$this->getUser()) {
            $this->denyAccessUnlessGranted(GlobalsVoter::ANONYMOUS, new HelperGlobals());
        }

        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        
        $tipo = $request->get('tipo');                
        $categoriapai = $request->get('categoriapai');        
        $status = $request->get('status');        
        
        // Flag que disgingue a forma de buscar as categorias
        $buscarApenasCategorias = $request->get('buscarApenasCategorias');

        // Busca as categorias de acordo com o tipo, categoria pai ou status
        if (!$buscarApenasCategorias) {
            $entities = $this->get('nasajon_mda.atendimento_categorias_repository')->findAll($tenant,$tipo,$categoriapai,$status);

            //Feito desta forma para desonerar o banco.
            foreach ($entities as $key => $entity) {
                $entities[$key]['breadcrumb'] = $entity['titulo_t2'] . ' - ' . $entity['titulo_t1'];
            }
        } else {

            // Busca os nomes de todas as categorias, subcategorias e seções.
            // Essa requisição é muito mais rápida, por não realizar os somatórios de quantitativos de artigos etc.
            // Por isso ela está sendo usada na renderização da Index.
            $entities = $this->get('nasajon_mda.atendimento_categorias_repository')->buscarApenasTitulosDasCategorias($tenant);
        }

        $response = new JsonResponse();
        $response->setData($entities);

        return $response;
    }
    
    
    /**
     * Lists all Atendimento\Categorias\artigos entities.
     *
     * @FOS\Get("/categoria/{categoriapai}/artigos", defaults={ "_format" = "json" })     
     */
    public function artigosAction($categoriapai, Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $tipoCategoria = $request->get('tipoCategoria');
        $tipoordenacao = $request->get('tipoordenacao');
        $entities = $this->get('nasajon_mda.atendimento_categorias_repository')->artigosPorCategoriaPai($tenant, $categoriapai, $tipoCategoria, $tipoordenacao);
        
        $response = new JsonResponse();
        $response->setData($entities);

        return $response;
    }
}
