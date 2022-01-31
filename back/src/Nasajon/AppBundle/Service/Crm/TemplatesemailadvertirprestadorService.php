<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Crm\TemplatesemailadvertirprestadorService as ParentService;
use LogicException;

class TemplatesemailadvertirprestadorService extends ParentService
{
    /**
    * @param string  $logged_user
    * @param string  $id_grupoempresarial
    * @param string  $tenant
    * @param \Nasajon\MDABundle\Entity\Crm\Templatesemailadvertirprestador $entity
    * @return string
    * @throws \Exception
    */
    public function update($logged_user,$id_grupoempresarial,$tenant, \Nasajon\MDABundle\Entity\Crm\Templatesemailadvertirprestador $entity){
        // Valido que só exista uma configuração por estabelecimento
        $this->validarDados($entity, $tenant, 'update');
        $entity->setResponderpara('noreply@nasajon.com.br');

        return parent::update($logged_user, $id_grupoempresarial, $tenant, $entity);
    }

    /**
    * @param string  $logged_user
    * @param string  $id_grupoempresarial
    * @param string  $tenant
    * @param \Nasajon\MDABundle\Entity\Crm\Templatesemailadvertirprestador $entity
    * @return string
    * @throws \Exception
    */
    public function insert($logged_user, $id_grupoempresarial, $tenant, \Nasajon\MDABundle\Entity\Crm\Templatesemailadvertirprestador $entity){
        // Valido que só exista uma configuração por estabelecimento
        $this->validarDados($entity, $tenant, 'insert');
        $entity->setResponderpara('noreply@nasajon.com.br');

        return parent::insert($logged_user, $id_grupoempresarial, $tenant, $entity);
    }

    protected function validarDados($entity, $tenant, $acao){
        $filter = new Filter();
        $filterExpression = $filter->getFilterExpression();
        array_push($filterExpression, new FilterExpression('estabelecimento', 'eq', $entity->getEstabelecimento()->getEstabelecimento()));
        $filter->setFilterExpression($filterExpression);

        $configuracoes = $this->findAll($tenant, $filter);

        $configuracoes = array_filter($configuracoes, function($config) use ($entity){
            return $config['templateemailadvertirprestador'] !== $entity->getTemplateemailadvertirprestador();
        });

        if (count($configuracoes) > 0) {
            throw new LogicException("Já existe uma configuração de template para este estabelecimento.", 1);
        }
    }
}