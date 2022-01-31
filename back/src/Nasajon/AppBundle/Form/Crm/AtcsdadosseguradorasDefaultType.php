<?php

namespace Nasajon\AppBundle\Form\Crm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Nasajon\MDABundle\Form\Crm\AtcsdadosseguradorasDefaultType as ParentType;

class AtcsdadosseguradorasDefaultType extends ParentType
{
    
    public function addDataTransformer(&$builder, $classe, $propriedade) {
        $builder
            ->get($propriedade)
            ->addModelTransformer(new \Symfony\Component\Form\CallbackTransformer(
                function ($data) use ($classe){
                    if (is_array($data)) {
                        return EntityHydrator::hydrate($classe, $data);
                    } else {
                        return $data;
                    }
                },
                function ($data) {
                    return $data;
                }
            ));
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
          */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Chamo função do pai para montar o form
        parent::buildForm($builder, $options);

        // Removo e readiciono campos de Lookup, para usar classe sobrescrita de validação de Lookup, devido a bug de busca de campos da entidade na entidade pai
        $builder->remove('seguradora');
        $builder->add('seguradora', \Nasajon\AppBundle\Type\LookupType::class, array(
            'label' => 'Seguradora',
            'required' => false,
            'error_bubbling' => true,
            'data_class' => 'Nasajon\MDABundle\Entity\Ns\Clientes',
            'property' => 'cliente',
            'service' => 'Nasajon\MDABundle\Service\Ns\ClientesService',
            'fixed' => ['tenant','id_grupoempresarial',],
            'constants' => [],
            'values' => [],
            // Essa propriedade faz com que a validação não busque os construtores na entidade pai, e sim na entidade atual.
            'use_parent_form_entity' => true
        ));
        $this->addDataTransformer($builder, 'Nasajon\MDABundle\Entity\Ns\Clientes', 'seguradora');

        $builder->remove('produtoseguradora');
        $builder->add('produtoseguradora', \Nasajon\AppBundle\Type\LookupType::class, array(
            'label' => 'Produto',
            'required' => true,
            'error_bubbling' => true,
            'data_class' => 'Nasajon\MDABundle\Entity\Crm\Templatespropostasgrupos',
            'property' => 'templatepropostagrupo',
            'service' => 'Nasajon\MDABundle\Service\Crm\TemplatespropostasgruposService',
            'fixed' => ['tenant','id_grupoempresarial'],
            'constants' => [],
            'values' => ['cliente' =>'seguradora',],
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\NotBlank([
                    'message' => 'O campo produto não pode ser vazio',
                ]),
            ],
            // Essa propriedade faz com que a validação não busque os construtores na entidade pai, e sim na entidade atual.
            'use_parent_form_entity' => true
        ));
        $this->addDataTransformer($builder, 'Nasajon\MDABundle\Entity\Crm\Templatespropostasgrupos', 'produtoseguradora');
        
        $builder->remove('apolice');
        $builder->add('apolice', \Nasajon\AppBundle\Type\LookupType::class, array(
            'label' => 'Apólice',
            'required' => true,
            'error_bubbling' => true,
            'data_class' => 'Nasajon\MDABundle\Entity\Crm\Templatespropostas',
            'property' => 'templateproposta',
            'service' => 'Nasajon\MDABundle\Service\Crm\TemplatespropostasService',
            'fixed' => ['tenant','id_grupoempresarial'],
            'constants' => [],
            'values' => ['templatepropostagrupo' =>'produtoseguradora',],
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\NotBlank([
                    'message' => 'O campo apólice não pode ser vazio',
                ]),
            ],
            // Essa propriedade faz com que a validação não busque os construtores na entidade pai, e sim na entidade atual.
            'use_parent_form_entity' => true
        ));
        $this->addDataTransformer($builder, 'Nasajon\MDABundle\Entity\Crm\Templatespropostas', 'apolice');
        
        $builder->remove('titularvinculo');
        $builder->add('titularvinculo', \Nasajon\AppBundle\Type\LookupType::class, array(
            'label' => 'Vínculo com o atendido',
            'required' => false,
            'error_bubbling' => true,
            'data_class' => 'Nasajon\MDABundle\Entity\Crm\Vinculos',
            'property' => 'vinculo',
            'service' => 'Nasajon\MDABundle\Service\Crm\VinculosService',
            'fixed' => ['tenant','id_grupoempresarial'],
            'constants' => [],
            'values' => [],
            // Essa propriedade faz com que a validação não busque os construtores na entidade pai, e sim na entidade atual.
            'use_parent_form_entity' => true
        ));
        $this->addDataTransformer($builder, 'Nasajon\MDABundle\Entity\Crm\Vinculos', 'titularvinculo');

        // Adicionando mensagem para validação de valores inválidos no campo Valor Autorizado dos dados do seguro
        $builder->remove('valorautorizado');
        $builder->add('valorautorizado', \Symfony\Component\Form\Extension\Core\Type\MoneyType::class, array(

            'label' => 'Valor Autorizado',
            'required' => true,
            'error_bubbling' => true,
            'invalid_message' => 'O campo Valor Autorizado dos Dados do Seguro possui um valor inválido!',

            'constraints' => [
                new \Symfony\Component\Validator\Constraints\NotBlank([
                    'message' => 'O campo valor autorizado não pode ser vazio',
                ]),
            ],

        ));

    }
}


