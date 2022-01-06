<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Doctrine\ORM\NoResultException;
use Exception;
use Nasajon\MDABundle\Entity\Atendimento\Equipes;
use Nasajon\MDABundle\Repository\Atendimento\EquipesclientesregrasRepository;
use Nasajon\MDABundle\Repository\Atendimento\EquipesRepository as ParentRepository;
use Nasajon\MDABundle\Repository\Atendimento\EquipesusuariosRepository;
use PDO;

/**
 * EquipesRepository
 *
 */
class EquipesRepository extends ParentRepository {
    /*
     * \Nasajon\MDABundle\Repository\Atendimento\EquipesusuariosRepository
     */

    protected $equipesusuariosRepo;

    /*
     * \Nasajon\MDABundle\Repository\Atendimento\EquipesclientesregrasRepository
     */
    protected $equipesclientesregrasRepo;

    public function __construct($connection, EquipesclientesregrasRepository $equipesclientesregrasRepo, EquipesusuariosRepository $equipesusuariosRepo) {
        parent::__construct($connection, $equipesclientesregrasRepo, $equipesusuariosRepo);
        $this->equipesusuariosRepo = $equipesusuariosRepo;
        $this->equipesclientesregrasRepo = $equipesclientesregrasRepo;
    }

    public function findQuery($id) {
        $sql = "SELECT

                                t0_.equipe as \"equipe\" ,
                                t0_.nome as \"nome\" ,
                                t0_.todosclientes as \"todosclientes\" ,
                                t0_.created_at as \"created_at\" ,
                                t0_.created_by as \"created_by\" ,
                                t0_.updated_at as \"updated_at\" ,
                                t0_.updated_by as \"updated_by\" ,
                                t0_.tenant as \"tenant\"

                FROM atendimento.equipes t0_



                WHERE t0_.equipe in (?)



               ";
        
        if (is_array($id)) {
          return $this->getConnection()->executeQuery($sql, [$id], [\Doctrine\DBAL\Connection::PARAM_STR_ARRAY]);
        } else {
          return $this->getConnection()->executeQuery($sql, [$id]);
        }
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
        
        $data['clientesregrasin'] = $this->tndmntQpsclntsrgrsRpstry->findAll($tenant, $id, 0);
        $data['clientesregrasex'] = $this->tndmntQpsclntsrgrsRpstry->findAll($tenant, $id, 1);
        $data['usuarios'] = $this->tndmntQpssrsRpstry->findAll($tenant, $id);
        return $data;
    }
    
    public function ObterFiltrosEquipe($id, $tenant) {
      $filtro = [];
      return array_map(function ($equipe) use ($tenant, $filtro) {
        $filtro['in'] = $this->tndmntQpsclntsrgrsRpstry->findAll($tenant, $equipe, 0);
        $filtro['ex'] = $this->tndmntQpsclntsrgrsRpstry->findAll($tenant, $equipe, 1);
        return $filtro;
      }, $id);
    }

    public function findByUsuario($tenant, $usuario) {
        $sql = "SELECT e.equipe
                FROM atendimento.equipes e
                JOIN atendimento.equipesusuarios eu ON eu.equipe = e.equipe
                WHERE eu.usuario = :usuario
                AND e.tenant = :tenant ";

        $equipe = $this->getConnection()->executeQuery($sql, ['tenant' => $tenant, 'usuario' => $usuario ])->fetchAll(\PDO::FETCH_COLUMN);

        if(empty($equipe)){
            return null;
        }
        
        return $this->ObterFiltrosEquipe($equipe, $tenant);;
    }

    /**
     * @param string  $tenant
     * @param string  $logged_user
     * @param Equipes $entity
     * @return string
     * @throws \Exception
     */
    public function insert($tenant, $logged_user, Equipes $entity) {

        $this->getConnection()->beginTransaction();
        try {
            $retorno = parent::insert($tenant, $logged_user, $entity);
            $entity->setEquipe($retorno['equipe']);

            foreach ($entity->getUsuarios() as $usuarios) {
                $usuarios->setEquipe($entity);
                $usuarios->setTenant($tenant);

                $this->equipesusuariosRepo->insert($entity->getEquipe(), $tenant, $logged_user, $usuarios);
            }


            foreach ($entity->getClientesregrasin() as $clientesregras) {
                $clientesregras->setEquipe($entity);
                $clientesregras->setTenant($tenant);

                $this->equipesclientesregrasRepo->insert($entity->getEquipe(), $tenant, $logged_user, $clientesregras);
            }

            foreach ($entity->getClientesregrasex() as $clientesregras) {
                $clientesregras->setEquipe($entity);
                $clientesregras->setTenant($tenant);

                $this->equipesclientesregrasRepo->insert($entity->getEquipe(), $tenant, $logged_user, $clientesregras);
            }

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
     * @param Equipes $entity
     * @return string
     * @throws \Exception
     */
    public function update($tenant, $logged_user, Equipes $entity, $originalEntity = null) {

        $this->getConnection()->beginTransaction();
        try {

            $sql_1 = "SELECT mensagem
            FROM atendimento.api_EquipeAlterar(row(
                                :equipe,
                                :nome,
                                :todosclientes,
                                :tenant,
                                :updated_by
                            )::atendimento.tequipealterar
            );";

            $stmt_1 = $this->getConnection()->prepare($sql_1);

            $stmt_1->bindValue("equipe", $entity->getEquipe());

            $stmt_1->bindValue("nome", $entity->getNome());

            $stmt_1->bindValue("todosclientes", $entity->getTodosclientes(), PDO::PARAM_INT);
            $stmt_1->bindValue("tenant", $tenant);

            $stmt_1->bindValue("updated_by", json_encode($logged_user));


            $stmt_1->execute();
            $resposta = $this->proccessApiReturn($stmt_1->fetchColumn(), $entity);

            $retorno = $resposta;


            $this->deleteUsuarios($entity, $tenant);
            $this->deleteClientesregras($entity, $tenant);

            foreach ($entity->getUsuarios() as $usuarios) {
                $usuarios->setEquipe($entity);
                $usuarios->setTenant($tenant);
                $this->equipesusuariosRepo->insert($entity->getEquipe(), $tenant, $logged_user, $usuarios);
            }

            foreach ($entity->getClientesregrasin() as $clientesregras) {
                $clientesregras->setEquipe($entity);
                $clientesregras->setTenant($tenant);

                $this->equipesclientesregrasRepo->insert($entity->getEquipe(), $tenant, $logged_user, $clientesregras);
            }

            foreach ($entity->getClientesregrasex() as $clientesregras) {
                $clientesregras->setEquipe($entity);
                $clientesregras->setTenant($tenant);

                $this->equipesclientesregrasRepo->insert($entity->getEquipe(), $tenant, $logged_user, $clientesregras);
            }


            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        return $retorno;
    }

    public function deleteUsuarios(Equipes $entity, $tenant) {

        $this->getConnection()->beginTransaction();
        try {

            $sql_1 = "SELECT mensagem
                      FROM atendimento.api_equipesusuariosexcluir(row(
                                :equipe,
                                :tenant
                            )::atendimento.tequipesusuariosexcluir
            );";

            $stmt_1 = $this->getConnection()->prepare($sql_1);
            $stmt_1->bindValue("equipe", $entity->getEquipe());
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

    public function deleteClientesregras(Equipes $entity, $tenant) {

        $this->getConnection()->beginTransaction();
        try {

            $sql_1 = "SELECT mensagem
                      FROM atendimento.api_equipesclientesregrasexcluir(row(
                                :equipe,
                                :tenant
                            )::atendimento.tequipesclientesregrasexcluir
            );";

            $stmt_1 = $this->getConnection()->prepare($sql_1);
            $stmt_1->bindValue("equipe", $entity->getEquipe());
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

    public function listUsuariosAlocadosEquipe($tenant) {
        $sql = "select eu.usuario, e.nome as equipe, eu.usuariotipo"
                ." from atendimento.equipesusuarios eu"
                ." join atendimento.equipes e ON e.equipe=eu.equipe"
                ." where eu.tenant = :tenant";

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute([
            'tenant' => $tenant
        ]);

        $response = $stmt->fetchAll();
        return $response;
    }

    public function getEquipes($tenant, $usuario) {
        $sql = "SELECT e.equipe, e.nome, eu.usuariotipo "
                ." FROM atendimento.equipesusuarios eu"
                ." INNER JOIN atendimento.equipes e on (e.equipe = eu.equipe)"
                ." WHERE usuario = :usuario and eu.tenant = :tenant";
        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute([
            'usuario' => $usuario,
            'tenant' => $tenant
        ]);

        $response = $stmt->fetchAll();

        return $response;
    }

}
