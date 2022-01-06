<?php
namespace Nasajon\Atendimento\AppBundle\Repository\Servicos;

use Doctrine\ORM\NoResultException;
use Nasajon\MDABundle\Repository\Servicos\AtendimentosregrasacoesRepository as ParentRepository;

class AtendimentosregrasacoesRepository extends ParentRepository
{
    /**
     * @param string $id
     * @param mixed $tenant
     * @param mixed $atendimentoregra
     * @return array
     * @throws NoResultException
     */
    public function find($id, $tenant = null, $atendimentoregra = null)
    {
        $data = $this->findQuery($id)->fetch();

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
        
        return $data;
    }

    private function findQuery($id)
    {
        $sql = "SELECT
                    t0_.atendimentoregraacao as \"atendimentoregraacao\" ,
                    t0_.acao as \"acao\" ,
                    t0_.acaocampocustomizado as \"acaocampocustomizado\" ,
                    t0_.valor as \"valor\" ,
                    t0_.tenant as \"tenant\" ,
                    t1_.atendimentoregra as \"t1_atendimentoregra\" ,
                    t1_.nome as \"t1_nome\" ,
                    t1_.ordem as \"t1_ordem\" ,
                    t1_.naoexecutarregrasubsequente as \"t1_naoexecutarregrasubsequente\" 
                FROM servicos.atendimentosregrasacoes t0_
                LEFT JOIN servicos.atendimentosregras t1_ ON t0_.atendimentoregra = t1_.atendimentoregra
                WHERE t0_.atendimentoregraacao = :id";

        $binds = [
            'id' => $id
        ];

        return $this->getConnection()->executeQuery($sql, $binds);
    }
}