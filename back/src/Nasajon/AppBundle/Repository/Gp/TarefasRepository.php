<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace Nasajon\AppBundle\Repository\Gp;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\MDABundle\Repository\AbstractRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Entity\Gp\Tarefas;
use Nasajon\MDABundle\Repository\Gp\TarefasRepository as ParentRepository;
use Nasajon\MDABundle\Entity\Gp\Funcoes;
use Nasajon\MDABundle\Entity\Estoque\Familias;
use Nasajon\MDABundle\Entity\Ns\Enderecos;
/**
 * TarefasRepository
 *
 */
class TarefasRepository extends ParentRepository
{

     /**
     * Retorna os guids das tarefas pelos ids dos escopos
     */
    public function findTarefasPorEscopos($idsEscopos, $tenant)
    {
        $sql = "select t.tarefa
                from gp.tarefas t
                left join gp.projetosescopo pe on pe.projetoescopo = t.projetoescopo and pe.tenant = t.tenant
                where t.tenant = :tenant
                and t.projetoescopo in $idsEscopos";
        $data = $this->getConnection()->executeQuery(
            $sql,
            ['tenant' => $tenant]
        )->fetchAll();
        return $data;
    }

    /**
     * Persiste os relacionamentos da tarefa com veículos, funções, famílias e endereços
     */
    public function persistirRelacionamentosTarefa($idTarefa, $tenant, $logged_user, $enderecos, $funcoes, $veiculos)
    {
        $tarefa = new Tarefas();
        $tarefa->setTarefa($idTarefa);
        // $this->persistirVeiculos($tarefa, $tenant, $logged_user, $veiculos);
        $this->persistirFuncoes($tarefa, $tenant, $logged_user, $funcoes);
        // $this->persistirFamilias($tarefa, $tenant, $logged_user, $familias);
        $this->persistirEnderecos($tarefa, $tenant, $logged_user, $enderecos);
    }

    // /**
    //  * implementar no futuro
    //  * Persiste os relacionamentos de tarefas com veículos
    //  */
    // private function persistirVeiculos($tarefa, $tenant, $logged_user, $veiculos)
    // {
    //     for ($i = 0; $i < count($veiculos); $i++) {
    //         $veiculo = new Veiculos();
    //         if (is_array($veiculos[$i]['veiculo'])) {
    //             $veiculo->setVeiculo($veiculos[$i]['veiculo']['veiculo']);
    //         } else {
    //             $veiculo->setVeiculo($veiculos[$i]['veiculo']);
    //         }
    //         $tarefaVeiculo = new TarefasVeiculos();
    //         $tarefaVeiculo->setTarefa($tarefa);
    //         $tarefaVeiculo->setVeiculo($veiculo);

    //         $this->gpTrfsvclsRpstry->insert($tarefa->getTarefa(), $tenant, $logged_user, $tarefaVeiculo);
    //     }
    // }

    /**
     * Persiste os relacionamentos de tarefas com funções
     */
    private function persistirFuncoes($tarefa, $tenant, $logged_user, $funcoes)
    {
        for ($i = 0; $i < count($funcoes); $i++) {
            $funcao = new Funcoes();
            if (is_array($funcoes[$i]['funcao'])) {
                $funcao->setFuncao($funcoes[$i]['funcao']['funcao']);
            } else {
                $funcao->setFuncao($funcoes[$i]['funcao']);
            }
            $tarefaFuncao['tarefa'] = $tarefa;
            $tarefaFuncao['funcao'] = $funcao;
            $this->insertTarefaFuncao($tarefa->getTarefa(), $tenant, $logged_user, $tarefaFuncao);
        }
    }

    /**
     * Persiste os relacionamentos de tarefas com famílias
     */
    private function persistirFamilias($tarefa, $tenant, $logged_user, $familias)
    {
        for ($i = 0; $i < count($familias); $i++) {
            $familia = new Familias();
            if (is_array($familias[$i]['familia'])) {
                $familia->setFamilia($familias[$i]['familia']['familia']);
            } else {
                $familia->setFamilia($familias[$i]['familia']);
            }
            $tarefaFamilia['tarefa'] = $tarefa;
            $tarefaFamilia['familia'] = $familia;
            $this->insertTarefaFamilia($tarefa->getTarefa(), $tenant, $logged_user, $tarefaFamilia);
        }
    }

    /**
     * Persiste os relacionamentos de tarefas com endereços
     */
    private function persistirEnderecos($tarefa, $tenant, $logged_user, $enderecos)
    {
        for ($i = 0; $i < count($enderecos); $i++) {
            $endereco = new Enderecos();
            if (is_array($enderecos[$i]['endereco'])) {
                $endereco->setEndereco($enderecos[$i]['endereco']['endereco']);
            } else {
                $endereco->setEndereco($enderecos[$i]['endereco']);
            }
            $tarefaEndereco['tarefa'] = $tarefa;
            $tarefaEndereco['endereco'] = $endereco;
            $tarefaEndereco['ordem'] = $enderecos[$i]['ordem'];
            $this->insertTarefaEndereco($tarefa->getTarefa(), $tenant, $logged_user, $tarefaEndereco);
        }
    }

    /**
     * @param string  $tarefa
     * @param string  $tenant
     * @param string  $logged_user
     * @param array   $entityArray
     * @return string 
     * @throws \Exception
     */
    public function insertTarefaFuncao($tarefa, $tenant, $logged_user, $entityArray)
    {

        $this->getConnection()->beginTransaction();
        try {
            $sql_1 = "SELECT mensagem
            FROM gp.api_tarefasfuncoesnovo_v2(row(
                    :tarefa,
                    :funcao,
                    :tenant,
                    :created_by
                )::gp.ttarefasfuncoesnovo_v2
            );";
            $stmt_1 = $this->getConnection()->prepare($sql_1);
            $stmt_1->bindValue("tarefa", $tarefa);
            $stmt_1->bindValue("funcao", isset($entityArray['funcao']) ? $entityArray['funcao']->getFuncao() : NULL);
            $stmt_1->bindValue("tenant", $tenant);
            $stmt_1->bindValue("created_by", json_encode($logged_user));
            $stmt_1->execute();
            $resposta = $this->processApiReturn($stmt_1->fetchColumn(), $entityArray);
            $retorno = $resposta;

            // $entity->setTarefafuncao($resposta);
            // $retorno = $this->find($retorno, $tarefa, $tenant);
            $sql_2 = "SELECT tarefafuncao FROM gp.tarefasfuncoes where tarefa = :tarefa and funcao = :funcao and tenant = :tenant;";
            $stmt_2 = $this->getConnection()->prepare($sql_2);
            $stmt_2->bindValue("tarefa", $tarefa);
            $stmt_2->bindValue("funcao", isset($entityArray['funcao']) ? $entityArray['funcao']->getFuncao() : NULL);
            $stmt_2->bindValue("tenant", $tenant);

            $stmt_2->execute();
            $retorno = $stmt_2->fetch(\PDO::FETCH_ASSOC);

            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
        return $retorno;
    }

    /**
     * @param string  $tarefa
     * @param string  $tenant
     * @param string  $logged_user
     * @param array   $entityArray
     * @return string 
     * @throws \Exception
     */
    public function insertTarefaFamilia($tarefa, $tenant, $logged_user, $entityArray)
    {
        $this->getConnection()->beginTransaction();
        try {
            $sql_1 = "SELECT mensagem
                FROM gp.api_tarefasfamiliasnovo_v2(row(
                        :tarefa,
                        :familia,
                        :tenant,
                        :created_by
                    )::gp.ttarefasfamiliasnovo_v2
                );";
            $stmt_1 = $this->getConnection()->prepare($sql_1);
            $stmt_1->bindValue("tarefa", $tarefa);
            $stmt_1->bindValue("familia", isset($entityArray['familia']) ? $entityArray['familia']->getFamilia() : NULL);
            $stmt_1->bindValue("tenant", $tenant);
            $stmt_1->bindValue("created_by", json_encode($logged_user));
            $stmt_1->execute();
            $resposta = $this->processApiReturn($stmt_1->fetchColumn(), $entityArray);
            $retorno = $resposta;

            // $entity->setTarefafamilia($resposta);
            // $retorno = $this->find($retorno, $tarefa, $tenant);
            $sql_2 = "SELECT tarefafamilia FROM gp.tarefasfamilias where tarefa = :tarefa and familia = :familia and tenant = :tenant;";
            $stmt_2 = $this->getConnection()->prepare($sql_2);
            $stmt_2->bindValue("tarefa", $tarefa);
            $stmt_2->bindValue("familia", isset($entityArray['familia']) ? $entityArray['familia']->getFamilia() : NULL);
            $stmt_2->bindValue("tenant", $tenant);

            $stmt_2->execute();
            $retorno = $stmt_2->fetch(\PDO::FETCH_ASSOC);

            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
        return $retorno;
    }

    /**
     * @param string  $tarefa
     * @param string  $tenant
     * @param string  $logged_user
     * @param array $entityArray
     * @return string 
     * @throws \Exception
     */
    public function insertTarefaEndereco($tarefa, $tenant, $logged_user, $entityArray)
    {
        try {
        $this->getConnection()->beginTransaction();
            $sql_1 = "SELECT mensagem
            FROM gp.api_tarefasenderecosnovo(row(
                    :tarefa,
                    :endereco,
                    :ordem,
                    :tenant,
                    :created_by
                )::gp.ttarefasenderecosnovo
            );";
            $stmt_1 = $this->getConnection()->prepare($sql_1);
            $stmt_1->bindValue("tarefa", $tarefa);
            $stmt_1->bindValue("endereco", isset($entityArray['endereco']) ? $entityArray['endereco']->getEndereco() : NULL);
            $stmt_1->bindValue("ordem", isset($entityArray['ordem']) ? $entityArray['ordem'] : NULL);
            $stmt_1->bindValue("tenant", $tenant);
            $stmt_1->bindValue("created_by", json_encode($logged_user));
            $stmt_1->execute();
            $resposta = $this->processApiReturn($stmt_1->fetchColumn(), $entityArray);
            $retorno = $resposta;

            // $entity->setTarefaendereco($resposta);
            // $retorno = $this->find($retorno, $tarefa, $tenant);
            $sql_2 = "SELECT tarefaendereco FROM gp.tarefasenderecos where tarefaendereco = :tarefaendereco and tenant = :tenant;";
            $stmt_2 = $this->getConnection()->prepare($sql_2);
            $stmt_2->bindValue("tarefaendereco", $resposta);
            $stmt_2->bindValue("tenant", $tenant);

            $stmt_2->execute();
            $retorno = $stmt_2->fetch(\PDO::FETCH_ASSOC);

            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
        return $retorno;
    }
    
    

    /**
     * @param string  $logged_user
     * @param string  $tenant
     * @param array $entityArray
     * @return string
     * @throws \Exception
     */
    public function alterar($logged_user, $tenant, $entityArray)
    {
        // $entityArr = $this->find($entity->getTarefa(), $tenant);
        // $originalEntity = $this->fillEntity($entityArr);
        $this->getConnection()->beginTransaction();
        try {
            $sql_1 = "SELECT mensagem
                FROM gp.api_tarefasalterar_v6(row(
                    :tarefa,
                    :updated_by,
                    :usuario,
                    :previsaoinicio,
                    :situacao,
                    :motivopausa,
                    :observacoespausa,
                    :tarefaassociadapausa,
                    :roteiro,
                    :adicionaraoescopodoprojeto,
                    :previsaotermino,
                    :observacao,
                    :inicio,
                    :termino,
                    :servicotecnico,
                    :propostaitem,
                    :valor,
                    :tenant
                )::gp.ttarefasalterar_v6
            );";
            $stmt_1 = $this->getConnection()->prepare($sql_1);
            $stmt_1->bindValue("tarefa", $entityArray['tarefa']);
            $stmt_1->bindValue("updated_by", json_encode($logged_user));
            $stmt_1->bindValue("usuario", isset($entityArray['usuario']) ? json_encode($entityArray['usuario']) : null);
            $stmt_1->bindValue("previsaoinicio", $entityArray['previsaoinicio']);
            $stmt_1->bindValue("situacao", $entityArray['situacao']);
            $stmt_1->bindValue("motivopausa", isset($entityArray['motivopausa']) ? $entityArray['motivopausa'] : null);
            $stmt_1->bindValue("observacoespausa", isset($entityArray['observacoespausa']) ? $entityArray['observacoespausa'] : null);
            $stmt_1->bindValue("tarefaassociadapausa", isset($entityArray['tarefaassociadapausa']) ? $entityArray['tarefaassociadapausa']['tarefa'] : null);
            $stmt_1->bindValue("roteiro", isset($entityArray['roteiro']) ? $entityArray['roteiro'] : null);
            $stmt_1->bindValue("adicionaraoescopodoprojeto", isset($entityArray['adicionaraoescopodoprojeto']) ? $entityArray['adicionaraoescopodoprojeto'] : null);
            $stmt_1->bindValue("previsaotermino", isset($entityArray['previsaotermino']) ? $entityArray['previsaotermino'] : null);
            $stmt_1->bindValue("observacao", isset($entityArray['observacao']) ? $entityArray['observacao'] : null);
            $stmt_1->bindValue("inicio", isset($entityArray['inicio']) ? $entityArray['inicio'] : null);
            $stmt_1->bindValue("termino", isset($entityArray['termino']) ? $entityArray['termino'] : null);
            $stmt_1->bindValue("servicotecnico", isset($entityArray['servicotecnico']) ? $entityArray['servicotecnico'] : null);
            $stmt_1->bindValue("propostaitem", isset($entityArray['propostaitem']) ? $entityArray['propostaitem'] : null);
            $stmt_1->bindValue("valor", isset($entityArray['valor']) ? $entityArray['valor'] : null);
            $stmt_1->bindValue("tenant", $tenant);
            $stmt_1->execute();
            $resposta = $this->processApiReturn($stmt_1->fetchColumn(), $entityArray);
            $retorno = $resposta;
            // $this->persistChildAnexos($originalEntity->getAnexos()->toArray(), $entity->getAnexos()->toArray(), $entity, $logged_user);
            // $this->persistChildComentarios($originalEntity->getComentarios()->toArray(), $entity->getComentarios()->toArray(), $entity, $logged_user);
            // $this->persistChildAjudantes($originalEntity->getAjudantes()->toArray(), $entity->getAjudantes()->toArray(), $entity, $logged_user);
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
        return $retorno;
    }

    public function findOSPorTarefa($idTarefa, $tenant)
    {
        $sql = "select count(1) from servicos.ordensservicos os
                left join gp.tarefasordensservicos tos on tos.ordemservico = os.ordemservico and tos.tenant = os.tenant
                left join gp.tarefas t on t.tarefa = tos.tarefa and tos.tenant = t.tenant               
                where os.situacao <> 5
                and t.tarefa = :id
                and t.tenant = :tenant
               ";
        $data = $this->getConnection()->executeQuery(
            $sql,
            [
                'id' => $idTarefa,
                'tenant' => $tenant
            ]
        )->fetch();
        return $data['count'];
    }

    private function overridenfindQuery(string $where, array $whereFields)
    {
        $sql = "SELECT

                                t0_.tarefa as \"tarefa\" ,
                                t0_.previsaoinicio as \"previsaoinicio\" ,
                                t0_.previsaotermino as \"previsaotermino\" ,
                                t0_.numero as \"numerotarefa\" ,
                                t0_.inicio as \"inicio\" ,
                                t0_.termino as \"termino\" ,
                                t0_.situacao as \"situacao\" ,
                                CASE t0_.situacao
                                    WHEN 0 THEN 'Pendente'::text
                                    WHEN 1 THEN 'Aberto'::text
                                    WHEN 2 THEN 'Em Andamento'::text
                                    WHEN 3 THEN 'Parado'::text
                                    WHEN 4 THEN 'Fechado'::text
                                    WHEN 5 THEN 'Cancelado'::text
                                    ELSE NULL::text
                                END as \"situacaostr\" ,
                                t0_.tenant as \"tenant\" ,
                                ( SELECT COALESCE(count(tos.ordemservico), 0::bigint) > 0
                                    FROM gp.tarefasordensservicos tos
                                   WHERE tos.tarefa = t0_.tarefa 
                                     AND tos.tenant = t0_.tenant 
                                     AND tos.ordemservico IS NOT NULL) as \"possui_ordemservico\" 
                FROM gp.tarefas t0_
        
        {$where}" ;

        return $this->getConnection()->executeQuery($sql, $whereFields);
    }

    /**
     * @param string $id
     * @param mixed $tenant
          
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id , $tenant){
    
        $where = $this->buildWhere();
        $data = $this->overridenfindQuery($where, [
                'id' => $id                    ,
                    'tenant' => $tenant
                                ])->fetch();

        $data = $this->adjustQueryData($data);
        
        return $data;
    }
}
