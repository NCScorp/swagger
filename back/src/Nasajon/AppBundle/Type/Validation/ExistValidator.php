<?php

namespace Nasajon\AppBundle\Type\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\Common\Inflector\Inflector;

class ExistValidator extends ConstraintValidator
{
    private $container;
    
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function validate($value, Constraint $constraint)
    {
        if(!(null === $value || '' === $value)) {
            $required = [$value];
            try
            {             
                $method = new \ReflectionMethod($this->container->get($constraint->service), "find");
                foreach($method->getParameters() as $param){
                    if(in_array($param->name, $constraint->fixed)) {
                        $required[] = $this->container->get('nasajon_mda.fixed_attributes')->get($param->name);
                    }
                    elseif(array_key_exists($param->name, $constraint->constants)) {
                        $required[] = $constraint->constants[$param->name];
                    }
                    elseif(array_key_exists($param->name, $constraint->values)) {
                        //Chama função para buscar construtor na entidade que engloba o objeto validado para buscar os construtores.
                        if ($constraint->use_parent_form_entity) {
                            $entityObj = $this->context->getObject()->getParent()->getParent()->getData();
                            $valorRequired = call_user_func(array($entityObj, sprintf('get%s', ucfirst(Inflector::camelize($constraint->values[$param->name])))));

                            // Se tipo for objeto, chamo função pra buscar a chave
                            if (gettype($valorRequired) == 'object'){
                                $valorRequired = call_user_func(array($valorRequired, sprintf('get%s', ucfirst(Inflector::camelize($param->name)))));
                            }

                            $required[] = $valorRequired;
                        }
                        //Funcionamento padrão da classe antes de sobrescrita: Chama a função da entidade relacionada ao formulário principal
                        else {
                            $entityObj = $this->context->getRoot()->getData();
                            $required[] = call_user_func(array($entityObj, sprintf('get%s', ucfirst(Inflector::camelize($constraint->values[$param->name])))));
                        }
                    }
                }
                call_user_func_array(array($this->container->get($constraint->service), "find"), $required);
            }
            catch (\Doctrine\ORM\NoResultException $e)
            {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{ string }', $value)
                    ->addViolation();
            }
        }
        
    }
}
