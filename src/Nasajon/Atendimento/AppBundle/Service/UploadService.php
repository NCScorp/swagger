<?php

namespace Nasajon\Atendimento\AppBundle\Service;

use Doctrine\ORM\EntityManager;
use Nasajon\Atendimento\AppBundle\Repository\Ns\AnexosmodulosRepository;
use Nasajon\MDABundle\Entity\Ns\Anexosmodulos;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Routing\Generator\UrlGenerator;

class UploadService {

    const ANEXO_MODULO_ATENDIMENTO = 1001;
    const ANEXO_MODULO_FOLLOWUP = 1002;

    /**
     *
     * @var AnexosmodulosRepository
     */
    private $anexosmodulosRepository;

    /**
     *
     * @var Router
     */
    private $router;

    /**
     *
     * @var EntityManager
     */
    private $em;

    public function __construct(AnexosmodulosRepository $anexosmodulosRepository, $router, EntityManager $em) {
        $this->anexosmodulosRepository = $anexosmodulosRepository;
        $this->router = $router;
        $this->em = $em;
    }

    public function findAll($tenant, $modulodoanexo, $id_modulodoanexo) {
        $anexos = $this->anexosmodulosRepository->findAll($tenant, $modulodoanexo, $id_modulodoanexo);

        if (count($anexos) > 0) {
            $tenantObj = $this->em->getRepository("Nasajon\ModelBundle\Entity\Ns\Tenants")->find($tenant);

            for ($i = 0; $i < count($anexos); $i++) {
                $mimetype = explode("/", $anexos[$i]['mimetype']);
                $anexos[$i]['tipo'] = (!empty($anexos[$i]['mimetype'])) ? (($mimetype[0] == 'image') ? 'image' : $mimetype[1]) : '';                
                $anexos[$i]['url'] = $this->router->generate(
                    'atendimento_cliente_assets_download',
                    [
                        "tenant" => $tenantObj->getCodigo(),
                        "id" => $anexos[$i]['anexomodulo'],
                        "hash" => $anexos[$i]['hash']
                    ],
                    UrlGenerator::ABSOLUTE_URL
                );
            }
        }

        return $anexos;
    }

    public function insert($modulodoanexo, $id_modulodoanexo, $tenant, $logged_user, Anexosmodulos $entity) {
        return $this->anexosmodulosRepository->insert($modulodoanexo, $id_modulodoanexo, $tenant, $logged_user, $entity);
    }

}
