<?php

namespace Nasajon\Atendimento\AppBundle\Service;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Nasajon\Atendimento\AppBundle\Builder\PHPExpressionBuilder;
use Nasajon\Atendimento\AppBundle\Builder\SQLExpressionBuilder;
use Nasajon\Atendimento\AppBundle\Repository\Admin\EquipesRepository;
use Nasajon\MDABundle\Repository\Atendimento\EquipesRepository as EquipesRepository2;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Nasajon\LoginBundle\Entity\Provisao;

/**
 * 
 * @author Rodrigo Dirk <rodrigodirk@nasajon.com.br>
 */
class EquipeClienteFilterService {

    /**
     *
     * @var EquipesRepository2
     */
    protected $equipeRepo;
    private $fixedAttributes;
    private $provisao;

    /**
     *
     * @param EquipesRepository2 $equipeRepo
     */
    public function __construct(EquipesRepository $equipeRepo, ParameterBag $fixedAttributes, Provisao $provisao) {
        $this->equipeRepo = $equipeRepo;
        $this->fixedAttributes = $fixedAttributes;
        $this->provisao = $provisao;
    }

    /**
     * @param string $alias
     * @return CompositeExpression
     */
    public function run($alias) {
        if (!$this->fixedAttributes->has('logged_user')) {
            return;
        }
        
        if ($this->provisao->getFuncaoCodigo() == 'admin') {
          return null;
        }

        $tenant = $this->fixedAttributes->get('tenant');
        $logged_user = $this->fixedAttributes->get('logged_user');

        $equipe = $this->equipeRepo->findByUsuario($tenant, $logged_user['email']);

        $builder = new SQLExpressionBuilder();
        return $builder->buildList($equipe, $alias);
    }

    public function evaluate($equipe, $cliente) {

        if (empty($cliente)) {
            return false;
        }

        $language = new ExpressionLanguage();
        $builder = new PHPExpressionBuilder();

        $estado = array_values(array_filter($cliente['enderecos'], function($endereco) {
                    return ($endereco['enderecopadrao']);
                }));

        return $language->evaluate($builder->build($equipe, null), array(
                    "estado" => !empty($estado) ? $estado[0]['uf'] : null,
                    "bloqueado" => $cliente['bloqueado'],
                    'vendedor' => !empty($cliente['vendedor']) ? $cliente['vendedor']['vendedor'] : null,
                    'representante' => !empty($cliente['representante']) ? $cliente['representante']['representantecomercial'] : null,
                    'representante_tecnico' => !empty($cliente['representante_tecnico']) ? $cliente['representante_tecnico']['representantetecnico'] : null,
        ));
    }

}
