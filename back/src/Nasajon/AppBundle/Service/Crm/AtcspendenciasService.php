<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace Nasajon\AppBundle\Service\Crm;

use DateTime;
use DateTimeZone;
use LogicException;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Service\Crm\AtcspendenciasService as ParentService;

class AtcspendenciasService extends ParentService
{

    public function __construct(\Nasajon\MDABundle\Repository\Crm\AtcspendenciasRepository $repository)
    {
        $this->repository = $repository;
    }

    private function processaDataPrazoPendencia ($dataPrazoPendencia) {
        // Comparando com a região da Bahia, pois a região de são paulo está aplicando horário de verão
        $agora = new DateTime('now', new DateTimeZone("America/Bahia"));        
        $dataPrazoPendencia = new DateTime($dataPrazoPendencia, new DateTimeZone("America/Bahia"));
        $prazo = ($dataPrazoPendencia->getTimestamp() - $agora->getTimestamp())/60 ; //$dataPrazoPendencia->diff($agora);
        $prazo = ceil($prazo);
        if($prazo <= 0 ){
            throw new LogicException("O Prazo para expiração da pendência não pode ser igual ou anterior ao momento atual.", 1);
        }
        return $prazo;
    }

    /**
     * @param string  $logged_user
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param \Nasajon\MDABundle\Entity\Crm\Atcspendencias $entity
     * @return string
     * @throws \Exception
     */
    public function insert($logged_user, $tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcspendencias $entity)
    {
        $prazo = $this->processaDataPrazoPendencia($entity->getDataprazopendencia());
        $entity->setPrazo($prazo);
        return parent::insert($logged_user, $tenant, $id_grupoempresarial,  $entity);
    }


    /**
     * @param string  $logged_user
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param \Nasajon\MDABundle\Entity\Crm\Atcspendencias $entity
     * @return string
     * @throws \Exception
     */
    public function update($logged_user, $tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcspendencias $entity)
    {
        try {
            $this->getRepository()->begin();
            
            //Buscando a pendência que está salva no banco de dados
            $pendOriginal = $this->find($entity->getNegociopendencia(), $tenant, $id_grupoempresarial);
            $prazo = 0;

            //Verificando se está fechando pendência. Se não estiver, preciso realizar verificação do prazo para expiração
            if($entity->getFecharpendencia() == false ){
                $prazo = $this->processaDataPrazoPendencia($entity->getDataprazopendencia());

            }
            //Se estiver fechando pendência, o prazo e a data do prazo para expiração se mantém
            else{
                $entity->setDataprazopendencia($pendOriginal['dataprazopendencia']);
                $prazo = $pendOriginal['prazo'];
            }
            
            $entity->setPrazo($prazo);

            $response = $this->getRepository()->update($logged_user, $tenant, $id_grupoempresarial,  $entity);

            $this->getRepository()->commit();

            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * TODO Quando for levar para o ERP APi cuidado com o findall foi totalmente sobrescrito com o novo formato de paginação
     * utilizando offset
     * @return array
     */
    public function findAll($tenant,$id_grupoempresarial, Filter $filter = null){

        $data = $this->getRepository()->findAll($tenant, $id_grupoempresarial, $filter);
        $paginate = null;

        if (count($data) && $filter !== null) {
            $countPaginate = $filter->getOffset()['paginate'] ?? 0;
            $paginate = [
                'count' => count($data) + $countPaginate,
                'full_count' => $data[0]['full_count']
            ];
        }

        return compact('data', 'paginate');            
    }


}
