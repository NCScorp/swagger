<?php

namespace Nasajon\AppBundle\Service\Ns;

use LogicException;
use Nasajon\MDABundle\Entity\Ns\Enderecos;
use Nasajon\MDABundle\Service\Ns\ClientesService as ParentService;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Entity\Ns\Clientes;
use Nasajon\MDABundle\Type\InvalidIdException;

/**
 * ClientesService sobrescrito para dar suporte aos telefones de contatos, visto que o MDA não dá suporte a objetos a partir do terceiro nível
 */
class ClientesService extends ParentService
{

    /**
     * @var \Nasajon\MDABundle\Service\Ns\TelefonesService
     */
    protected $tlfnsSrvc;

    /**
     * @var \Nasajon\MDABundle\Service\Web\ConfiguracoesService
     */
    protected $wbCnfgrcsSrvc;

    /**
     * Sobrescrevendo o construtor para usar o service de telefones
     */
    public function __construct(\Nasajon\MDABundle\Repository\Ns\ClientesRepository $repository, $crmTmpltsprpstsgrpsSrvc, $nsClntsnxsSrvc, $nsClntsdcmntsSrvc, $nsCnttsSrvc, $nsNdrcsSrvc, $nsPssstpstvddsSrvc, $tlfnsSrvc, $wbCnfgrcsSrvc){

        parent::__construct($repository, $crmTmpltsprpstsgrpsSrvc, $nsClntsnxsSrvc, $nsClntsdcmntsSrvc, $nsCnttsSrvc, $nsNdrcsSrvc, $nsPssstpstvddsSrvc);
        $this->tlfnsSrvc = $tlfnsSrvc;
        $this->wbCnfgrcsSrvc = $wbCnfgrcsSrvc;
    }

    /**
     * Método sobrescrito para recuperar os telefones dos contatos
     * @param string $id
     * @param mixed $tenant
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id , $tenant, $id_grupoempresarial){
        $data = parent::find($id, $tenant, $id_grupoempresarial);
        $data['diasparavencimento'] = $data['diasparavencimento'] ? $data['diasparavencimento'] : $this->wbCnfgrcsSrvc->getValor($tenant, 'DIASPARAVENCIMENTO');
        if (isset($data['contatos'])) {
            $data['contatos'] = array_map(function (&$contato) use ($tenant) {
                $contato['telefones'] = $this->tlfnsSrvc->findAll($tenant, $contato['contato']);
                return $contato;
            }, $data['contatos']);
        }
        $data['endereco'] = $this->nsNdrcsSrvc->findAll($tenant,$id);
        return $data;
    }

    /**
     * Sobrescrevendo para preencher o telefone, que é um objeto interno ao contato
     * @param array $data
     * @return type
     */
    public function fillEntity($data)
    {
        /* Sobrescrevendo para preencher o telefone, que é um objeto interno ao contato
           O telefone de um contato estava sendo limpo ao adicionar um telefone em outro contato  */
        if (isset($data['contatos'])) {
            foreach ($data['contatos'] as &$contato) {
                if (isset($contato['telefones'])) {
                    foreach ($contato['telefones'] as &$telefone) {
                        $telefone = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Telefones', $telefone);
                    }
                    unset($telefone); //destroy referencia após loop
                    $contato['telefones'] = new \Doctrine\Common\Collections\ArrayCollection($contato['telefones']);
                }
            }
            unset($contato); //destroy referencia após loop
        }
        /* Sobrescrevendo para preencher o estado, municipio e cidade estrangeira quando não são mostrados
           O cidade estrangeira não estava passando pelo hydrate quando o pais era o Brasil, e o mesmo acontecia a municipio e estado com um pais estrangeiro  */
           if( array_key_exists('endereco', $data))
           {
               for ($i = 0; $i < count($data['endereco']); $i++) {
                      $data['endereco'][$i]['cidadeestrangeira']= EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Cidadesestrangeiras', $data['endereco'][$i]['cidadeestrangeira']);
                      $data['endereco'][$i]['estado']= EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Estados', $data['endereco'][$i]['estado']);
                      $data['endereco'][$i]['municipio']= EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Municipios', $data['endereco'][$i]['municipio']);
               }
           }
        return parent::fillEntity($data);
    }

    /* Sobrescrevendo para id_pessoa ser enviado corretamente e fazer relação entre clientes e endereços */
    protected function persistChildEndereco($oldList, $newList, $entity, $tenant)
    {

        $qtd_locais = array_filter(
            $newList,
            function ($itemFilter) {
                return $itemFilter->getTipoendereco() == 0;
            }
        );

        if (count($qtd_locais) > 1) {

            throw new LogicException('Não é possível ter mais de um endereço local. Verifique!');
        }

        $qtd_cobrancas = array_filter(
            $newList,
            function ($itemFilter) {
                return $itemFilter->getTipoendereco() == 2;
            }
        );

        if (count($qtd_cobrancas) > 1) {

            throw new LogicException('Não é possível ter mais de um endereço de cobrança. Verifique!');
        }


        foreach ($newList as $item) {
            $item->setIdpessoa($entity->getCliente());
        }
        $data = parent::persistChildEndereco($oldList, $newList, $entity, $tenant);
    }

    /*Sobreescrito para fazer o CPF ser registrado no mesmo campo que CNPJ no banco. */
    public function insert($id_grupoempresarial, $logged_user, $tenant, \Nasajon\MDABundle\Entity\Ns\Clientes $entity)
    {
        if ($entity->getCadastro() === "2") {
            $entity->setCnpj($entity->getCpf());
        }
        $this->validateEnderecos($entity);

        return parent::insert($id_grupoempresarial, $logged_user, $tenant, $entity);
    }


    public function update($logged_user, $tenant, $id_grupoempresarial, Clientes $entity, $originalEntity = null)
    {
        $this->validateEnderecos($entity);
        return parent::update($logged_user, $tenant, $id_grupoempresarial, $entity, $originalEntity);
    }

    /**
     * Validando os enderecos passado, caso não seja informado nenhum endereco não será validado
     * porém se existir algum endereco, obriga a ter tanto do tipo local e cobrança
     *
     * @param Clientes $entity
     */
    private function validateEnderecos(Clientes $entity): void
    {
        if (count($entity->getEndereco())) {

            $flagEndLocal = $flagEndCobranca = 0;
            /**
             * @var $endereco Enderecos
             */
            foreach ($entity->getEndereco() as $endereco) {
                if ($endereco->getTipoendereco() == 0) { // endereco do tipo local
                    $flagEndLocal = 1;
                } else if ($endereco->getTipoendereco() == 2) { //Endereco do tipo cobranca
                    $flagEndCobranca = 1;
                }
            }

            if (!$flagEndLocal) {
                throw new \LogicException("Endereço do tipo local não informado");
            }
            if (!$flagEndCobranca) {
                throw new \LogicException("Endereço do tipo cobrança não informado");
            }

        }
    }
}