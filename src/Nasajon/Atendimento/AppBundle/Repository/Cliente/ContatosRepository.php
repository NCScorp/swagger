<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace Nasajon\Atendimento\AppBundle\Repository\Cliente;

use Doctrine\DBAL\Query\Expression\CompositeExpression;

use Nasajon\Atendimento\AppBundle\Exception\InvalidObjectException;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Contatos;
use Nasajon\MDABundle\Repository\Atendimento\Cliente\ContatosRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;
use \Nasajon\MDABundle\Entity\Atendimento\Historicoscontatosclientes;

/**
* ContatosRepository
*
*/
class ContatosRepository extends ParentRepository {

    private $nsContatosRepository;
    private $nsContatosEmailsRepository;
    private $nsTelefonesRepository;
    private $atendimentoHistoricosContatosEmails;
    private $configService;
    private $fixedAttributes;

    public function __construct($connection, $nsContatosRepository, $nsContatosEmailsRepository, $nsTelefonesRepository, $atendimentoHistoricosContatosEmails, $configService, $fixedAttributes) {

        parent::__construct($connection, $nsContatosRepository, $nsContatosEmailsRepository, $nsTelefonesRepository);
        
        $this->nsContatosRepository = $nsContatosRepository;
        $this->nsContatosEmailsRepository = $nsContatosEmailsRepository;
        $this->nsTelefonesRepository = $nsTelefonesRepository;
        $this->atendimentoHistoricosContatosEmails = $atendimentoHistoricosContatosEmails;
        $this->configService = $configService;
        $this->fixedAttributes = $fixedAttributes;
    }

    private function findEmailPrincipal($id, $tenant) {
        $sql = "SELECT string_agg(cep.email, ', ') email 
                    FROM ns.contatosemails cep 
                WHERE cep.principal = true AND cep.pessoa_id = :id  AND cep.tenant = :tenant
                GROUP BY cep.pessoa_id";

        $email = $this->getConnection()
                    ->executeQuery($sql, [
                        "id" => $id,
                        "tenant" => $tenant])
                    ->fetch();

        if ($email && isset($email['email'])) {
            return $email['email'];
        }

        return null;
    }

    private function findEmailCobranca($id, $tenant) {
        $sql = "SELECT string_agg(cec.email, ', ') email 
                FROM ns.contatosemails as cec 
                WHERE (cec.recebe_nfe = true OR cec.envia_nfse_prefeitura = true OR cec.recebe_boleto = true OR cec.recebe_mala_direta = true)
                AND cec.pessoa_id = :id AND cec.tenant = :tenant
                GROUP BY cec.pessoa_id";

        $email = $this->getConnection()
                    ->executeQuery($sql, [
                        "id" => $id,
                        "tenant" => $tenant])
                    ->fetch();

        if ($email && isset($email['email'])) {
            return $email['email'];
        }

        return null;
    }

    /**
     * @return array
     */
    public function findAll($tenant, $conta= "",  Filter $filter = null){

        $result = parent::findAll($tenant, $conta, $filter);

        foreach ($result as &$cliente) {
            $cliente['contatos'] = $this->nsContatosRepository->findAll($tenant, $cliente['id']);

            // Busca os e-mails principal e cobrança na tabela ns.contatosemails.
            // Esses e-mails são salvos com algumas configurações e informações extras,
            // Ex.: - se a coluna recebe_boleto for true, esse e-mail é de cobrança.
            //      - se a coluna principal for true, então, esse é o e-mail principal.
            $cliente['emailprincipal'] = $this->findEmailPrincipal($cliente['id'], $tenant);
            $cliente['emailcobranca'] = $this->findEmailCobranca($cliente['id'], $tenant);
            
            $telefones = $this->nsTelefonesRepository->findAll($tenant, $cliente['id']);
            $contatosEmails = $this->nsContatosEmailsRepository->findAll($tenant, $cliente['id']);

            foreach ($cliente['contatos'] as &$contato) {
                $contato['telefones'] = array_values(array_filter($telefones, function($telefone) use ($contato) {
                    return $telefone['contato'] == $contato['id'];
                }));

                $contato['contatosemails'] = array_values(array_filter($contatosEmails, function($contatoEmail) use ($contato) {
                    return $contatoEmail['contato'] == $contato['id'];
                }));
            }
        }

        return $result;
    }

    public function findAllQueryBuilderBody(&$queryBuilder, $tenant, $conta = "", Filter $filter = null) {
        $binds = [];
        $where = [];

        $where[] = $queryBuilder->expr()->eq("t0_.tenant", "?");
        $binds[] = $tenant;

        // Filtra pela conta do usuário logado.
        $where[] = $queryBuilder->expr()->eq("cf.conta", "?");
        $binds[] = $conta;

        // Filtra pelos clientes cujo usuário logado é Administrador.
        $where[] = $queryBuilder->expr()->eq("cf.funcao", "?");
        $binds[] = "A";

        // Filtra onde o usuário logado não está pendente como usuário do cliente.
        $where[] = $queryBuilder->expr()->eq("cf.pendente", "?");
        $binds[] = 'false';

        list($filters, $filtersBinds) = $this->proccessFilter($filter);
        $binds = array_merge($binds, $filtersBinds);

        if (!empty($where)) {
            $queryBuilder->where(new CompositeExpression(CompositeExpression::TYPE_AND, $where));
        }

        if (!empty($filters)) {
            $queryBuilder->andWhere(new CompositeExpression(CompositeExpression::TYPE_OR, $filters));
        }

        // Reseta a cláusula from do QueryBuilder porque essa cláusula é determinada de acordo com a configuração em web.configuracoes.
        // Como essa cláusula é adicionada na função findAllQueryBuilder, se não resetar, dá conflito por ter duas cláusulas from.
        $queryBuilder->resetQueryPart('from');

        $config = $this->configService->get($tenant, 'ATENDIMENTO', 'GRUPOS_EMPRESARIAIS_ATIVOS');

        if (!$config) {
            $queryBuilder->from('ns.vwclientes_atendimento', 't0_');

        } else {
            $queryBuilder->from('ns.vwclientes_atendimento_v2', 't0_');
        }

        $queryBuilder->join('t0_', 'atendimento.clientesfuncoes', 'cf', 'cf.cliente = t0_.id');

        $queryBuilder->orderBy('t0_.nomefantasia');

        return $binds;
    }

    private function findQuery($id, $tenant)
    {
        $sql = "SELECT
                    t0_.id as id,
                    t0_.id_pessoa as cliente,
                    t0_.email as conta,
                    t0_.nome as nome,
                    t0_.cargo as cargo,
                    t0_.email as emailprincipal,
                    t0_.observacao as observacao,
                    t0_.principal as principal,
                    t0_.tenant as tenant
            
                FROM ns.contatos t0_

                WHERE t0_.id = :id AND t0_.tenant = :tenant
                
                ORDER BY t0_.nome;";

        return $this->getConnection()->executeQuery($sql, [
            'id' => $id, 'tenant' => $tenant
        ]);
    }

    /**
     * @param string $id
     * @param mixed $tenant
          
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $tenant) {

        $data = $this->findQuery($id, $tenant)->fetch();

        if (!$data) {
            throw new \Doctrine\ORM\NoResultException();
        }
        
        foreach ($this->getLinks() as $link) {
            $newArr = [];

            foreach ($data as $subKey => $value) {
                if (substr($subKey, 0, strlen($link['alias'])) === $link['alias']) {
                    $newArr[str_replace($link['alias'], "", $subKey)] = $value;
                    unset($data[$subKey]);
                }
            }

            if (is_null($newArr[$link['identifier']])) {
                $data[$link['field']] = null;
            } else {
                $data[$link['field']] = $newArr;
            }
        }
        
        $data['telefones'] = array_values(array_filter($this->nsTlfnsRpstry->findAll($tenant, $data['cliente']), function($telefone) use ($data) {
            return $telefone['contato'] == $data['id'];
        }));

        $data['contatosemails'] = array_values(array_filter($this->nsContatosEmailsRepository->findAll($tenant, $data['cliente']), function($contatoEmail) use ($data) {
            return $contatoEmail['contato'] == $data['id'];
        }));

        $data['contatos'] = [];

        return $data;
    }

    private function persistirTelefones(&$telefones, $cliente, $idContato, $tenant, $loggedUser) {
        foreach ($telefones as &$telefone) {
            $telefone['principal'] = (isset($telefone['principal']) && $telefone['principal']) ? 'true' : 'false';
            
            $telefone['tptelefone'] = (isset($telefone['tptelefone']) && strlen($telefone['tptelefone']) > 0) ? $telefone['tptelefone'] : null;

            $entidadeTelefone = $this->nsTelefonesRepository->fillEntity($telefone);
            $entidadeTelefone->setContato($idContato);

            if ($entidadeTelefone->getId()) {
                $this->nsTelefonesRepository->update($cliente, $loggedUser, $tenant, $entidadeTelefone);

            } else {
                $this->nsTelefonesRepository->insert($cliente, $loggedUser, $tenant, $entidadeTelefone);
                $telefone['id'] = $entidadeTelefone->getId();
            }
        }
    }

    private function persistirContatosEmails(&$contatosEmails, $cliente, $idContato, $tenant, $loggedUser) {
        foreach ($contatosEmails as &$contatoEmail) {
            
            if (isset($contatoEmail['tipo'])) {
                if ($contatoEmail['tipo'] == 1) {
                    $contatoEmail['principal'] = true;
                    
                } else if ($contatoEmail['tipo'] == 2) {
                    $contatoEmail['recebe_boleto'] = true;
                }
            }

            $entidadeContatosEmails = $this->nsContatosEmailsRepository->fillEntity($contatoEmail);
            $entidadeContatosEmails->setContato($idContato);
            
            if (!$entidadeContatosEmails->getPrincipal()) {
                $entidadeContatosEmails->setPrincipal(false);
            }

            if (!$entidadeContatosEmails->getRecebeboleto()) {
                $entidadeContatosEmails->setRecebeboleto(false);
            }

            if ($entidadeContatosEmails->getId()) {
                $this->nsContatosEmailsRepository->update($loggedUser, $tenant, $entidadeContatosEmails);

            } else {
                $this->nsContatosEmailsRepository->insert($cliente, $loggedUser, $tenant, $entidadeContatosEmails);
                $contatoEmail['id'] = $entidadeContatosEmails->getId();
            }
        }
    }

    private function validarObjeto($contato) {
        if (!isset($contato['nome']) || !$contato['nome']) {
            throw new InvalidObjectException("É necessário preencher o campo nome!", 400);
        }

        if (isset($contato['telefones'])) {
            foreach ($contato['telefones'] as $telefone) {
                if (!isset($telefone['ddd']) || !$telefone['ddd']) {
                    throw new InvalidObjectException("É necessário preencher o campo DDD do telefone!", 400);
                }

                if (!isset($telefone['telefone']) || !$telefone['telefone']) {
                    throw new InvalidObjectException("É necessário preencher o campo telefone!", 400);
                }
            }
        }
    }

    public function inserirContatos($entityArray, $tenant, $loggedUser) { 

        $this->validarObjeto($entityArray);

        $entityArray['contatos'] = [];
        $entityArray['contatosemails'] = (isset($entityArray['contatosemails']) && $entityArray['contatosemails']) ? $entityArray['contatosemails'] : [];
        $entityArray['telefones'] = (isset($entityArray['telefones']) && $entityArray['telefones']) ? $entityArray['telefones'] : [];
        $entidade = $this->fillEntity($entityArray);
        $entidade->setIdpessoa($entityArray['cliente']);

        $telefones = $entityArray['telefones'];

        $this->getConnection()->beginTransaction();

        $this->insert($loggedUser, $tenant, $entidade);

        $entityArray['id'] = $entidade->getId();

        $this->persistirContatosEmails($entityArray['contatosemails'], $entidade->getIdPessoa(), $entidade->getId(), $tenant, $loggedUser);
        $this->persistirTelefones($entityArray['telefones'], $entidade->getIdPessoa(), $entidade->getId(), $tenant, $loggedUser);

        $historico = new Historicoscontatosclientes();
        $historico->setContato($entidade->getId());
        $historico->setCliente($entidade->getIdPessoa());
        $historico->setAcao(0);
        $historico->setValornovo(json_encode($entityArray));

        $this->atendimentoHistoricosContatosEmails->insert($loggedUser, $tenant, $historico);

        $this->getConnection()->commit();
    }

    public function alterarContatos($entityArray, $tenant, $loggedUser) { 

        $this->validarObjeto($entityArray);

        $entityArray['contatos'] = [];
        $entidade = $this->fillEntity($entityArray);
        $entidade->setIdpessoa($entityArray['cliente']);
        $entidade->setEmailprincipal($entityArray['conta']);

        $valorAntigo = $this->find($entityArray['id'], $tenant);

        $this->getConnection()->beginTransaction();

        $this->update($loggedUser, $tenant, $entidade);

        $this->persistirContatosEmails($entityArray['contatosemails'], $entidade->getIdPessoa(), $entidade->getId(), $tenant, $loggedUser);
        $this->persistirTelefones($entityArray['telefones'], $entidade->getIdPessoa(), $entidade->getId(), $tenant, $loggedUser);

        $historico = new Historicoscontatosclientes();
        $historico->setContato($entidade->getId());
        $historico->setCliente($entidade->getIdPessoa());
        $historico->setAcao(1);
        $historico->setValorantigo(json_encode($valorAntigo));
        $historico->setValornovo(json_encode($entityArray));

        $this->atendimentoHistoricosContatosEmails->insert($loggedUser, $tenant, $historico);

        $this->getConnection()->commit();
    }

    public function delete($tenant, Contatos $entity) {
        $this->getConnection()->beginTransaction();

        // Busca o contato, porque a $entity não chega com os dados do cliente.
        $contato = $this->find($entity->getId(), $tenant);

        $retorno = parent::delete($tenant, $entity);

        $historico = new Historicoscontatosclientes();
        $historico->setContato($contato['id']);
        $historico->setCliente($contato['cliente']);
        $historico->setAcao(2);
        $historico->setValornovo(json_encode(""));

        $this->atendimentoHistoricosContatosEmails->insert($this->fixedAttributes->get("logged_user"), $tenant, $historico);

        $this->getConnection()->commit();

        return $retorno;
    }
}