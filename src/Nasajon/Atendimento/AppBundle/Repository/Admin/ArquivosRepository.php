<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Doctrine\ORM\NoResultException;
use Exception;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Arquivos;
use Nasajon\MDABundle\Repository\Atendimento\Admin\ArquivosregrasRepository;
use Nasajon\MDABundle\Repository\Atendimento\Admin\ArquivosRepository as ParentRepository;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use PDO;

/**
 * ArquivosRepository
 *
 */
class ArquivosRepository extends ParentRepository {
    protected $arquivosregrasRepo;
    private $configService;

    public function __construct(
        $connection, 
        ArquivosregrasRepository $arquivosregrasRepo,
        ConfiguracoesService $configService
    ) 
    {
        parent::__construct($connection, $arquivosregrasRepo);
        $this->arquivosregrasRepo = $arquivosregrasRepo;
        $this->configService = $configService;
    }

    public function verificaPermissao($tenant) {
        $config = $this->configService->get($tenant, 'ATENDIMENTO', 'DISPONIBILIZAR_ARQUIVOS');
  
        if (!$config) {
          return false;
        }
  
        return true;
    }

    public function findQuery($id) {
        $sql = "SELECT

                               t0_.arquivo as \"arquivo\" ,
                                t0_.titulo as \"titulo\" ,
                                t0_.nomearquivo as \"nomearquivo\" ,
                                t0_.tamanho as \"tamanho\" ,
                                t0_.caminho as \"caminho\" ,
                                t0_.situacao as \"situacao\" ,
                                t0_.todosclientes as \"todosclientes\" ,
                                t0_.tenant as \"tenant\" ,
                                t0_.created_by as \"created_by\" ,
                                t0_.created_at as \"created_at\" ,
                                t0_.updated_by as \"updated_by\" ,
                                t0_.updated_at as \"updated_at\" ,
                                t0_.regravisualizacao as \"regravisualizacao\",
                                t0_.descricao as \"descricao\"

                FROM atendimento.arquivos t0_



                WHERE t0_.arquivo = :id



               ";

        return $this->getConnection()->executeQuery($sql, [
                    'id' => $id
        ]);
    }

    /**
     * @param string $id
     * @param mixed $tenant

     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $tenant) {

        $data = $this->findQuery($id)->fetch();

        if (!$data) {
            throw new NoResultException();
        }

        $data['created_by'] = json_decode($data['created_by'], true);
        $data['updated_by'] = json_decode($data['updated_by'], true);

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

        $tamanhoAux = $data['tamanho'] / 1024;

        if ($tamanhoAux <= 1024) {
            $data['multiplicador_tamanho'] = 1;
        } else if ($tamanhoAux <= 1048576) {
            $data['multiplicador_tamanho'] = 1024;
        } else if ($tamanhoAux <= 1073741824) {
            $data['multiplicador_tamanho'] = 1048576;
        } else {
            $data['multiplicador_tamanho'] = 1073741824;
        }

        $data['tamanho'] = $tamanhoAux / $data['multiplicador_tamanho'];

        $data['arquivosregrasin'] = $this->tndmntDmnRqvsrgrsRpstry->findAll($tenant, $id, 1);
        $data['arquivosregrasex'] = $this->tndmntDmnRqvsrgrsRpstry->findAll($tenant, $id, 0);
        return $data;
    }

    /**
     * @param string  $tenant
     * @param string  $logged_user
     * @param Arquivos $entity
     * @return string
     * @throws \Exception
     */
    public function insert($tenant, $logged_user, Arquivos $entity) {

        $this->getConnection()->beginTransaction();
        try {
            $sql_1 = "SELECT mensagem
    FROM atendimento.api_ArquivoNovo(row(
            :titulo,
            :nomearquivo,
            :tamanho,
            :caminho,
            :todosclientes,
            :regravisualizacao,
            :tenant,
            :created_by,
            :descricao
        )::atendimento.tarquivonovo
    );";

            $stmt_1 = $this->getConnection()->prepare($sql_1);

            $stmt_1->bindValue("titulo", $entity->getTitulo());

            $stmt_1->bindValue("nomearquivo", $entity->getNomearquivo());

            $stmt_1->bindValue("tamanho", $entity->getTamanho());

            $stmt_1->bindValue("caminho", $entity->getCaminho());

            $stmt_1->bindValue("todosclientes", $entity->getTodosclientes());

            $stmt_1->bindValue("regravisualizacao", $entity->getRegravisualizacao());

            $stmt_1->bindValue("tenant", $tenant);

            $stmt_1->bindValue("created_by", json_encode($logged_user));
            
            $stmt_1->bindValue("descricao", $entity->getDescricao());

            $stmt_1->execute();
            $resposta = $this->proccessApiReturn($stmt_1->fetchColumn(), $entity);

            $retorno = $resposta;

            $entity->setArquivo($resposta);

            foreach ($entity->getArquivosregrasin() as $arquivosregras) {
                $arquivosregras->setArquivo($entity);
                $arquivosregras->setTenant($tenant);

                $this->arquivosregrasRepo->insert($entity->getArquivo(), $tenant, $logged_user, $arquivosregras);
            }

            foreach ($entity->getArquivosregrasex() as $arquivosregras) {
                $arquivosregras->setArquivo($entity);
                $arquivosregras->setTenant($tenant);

                $this->arquivosregrasRepo->insert($entity->getArquivo(), $tenant, $logged_user, $arquivosregras);
            }

            $retorno = $this->find($retorno, $tenant);

            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        return $retorno;
    }

    /**
     * @param string  $tenant
     * @param string  $logged_user
     * @param Arquivos $entity
     * @return string
     * @throws \Exception
     */
    public function update($tenant, $logged_user, Arquivos $entity, $originalEntity = null) {
        $this->getConnection()->beginTransaction();
        try {
            $retorno = parent::update($tenant, $logged_user, $entity);

            $this->deleteArquivosregras($entity, $tenant);

            foreach ($entity->getArquivosregrasin() as $arquivosregras) {
                $arquivosregras->setArquivo($entity);
                $arquivosregras->setTenant($tenant);

                $this->arquivosregrasRepo->insert($entity->getArquivo(), $tenant, $logged_user, $arquivosregras);
            }

            foreach ($entity->getArquivosregrasex() as $arquivosregras) {
                $arquivosregras->setArquivo($entity);
                $arquivosregras->setTenant($tenant);

                $this->arquivosregrasRepo->insert($entity->getArquivo(), $tenant, $logged_user, $arquivosregras);
            }

            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        return $retorno;
    }

    public function deleteArquivosregras(Arquivos $entity, $tenant) {

        $this->getConnection()->beginTransaction();
        try {

            $sql_1 = "SELECT mensagem
                      FROM atendimento.api_arquivosregrasexcluir(row(
                                :arquivo,
                                :tenant
                            )::atendimento.tarquivosregrasexcluir
            );";

            $stmt_1 = $this->getConnection()->prepare($sql_1);
            $stmt_1->bindValue("arquivo", $entity->getArquivo());
            $stmt_1->bindValue("tenant", $tenant);
            $stmt_1->execute();
            $resposta = $this->proccessApiReturn($stmt_1->fetchColumn(), $entity);
            $retorno = $resposta;
            $this->getConnection()->commit();
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

}
