<?php

namespace Nasajon\AppBundle\Form\Crm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Nasajon\MDABundle\Form\Crm\ListadavezconfiguracoesDefaultType as ParentType;

class ListadavezconfiguracoesDefaultType extends ParentType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options){
        parent::buildForm($builder, $options);

        // Removo campo "idlistadavezregravalor" e adiciono usando o lookupType sobrescrito
        $builder->remove('idlistadavezregravalor');
        $optionsNovo =  [
            'label' => '',
            'required' => false,
            'error_bubbling' => true,
            'use_parent_form_entity' => true,
            'data_class' => 'Nasajon\MDABundle\Entity\Crm\Listadavezregrasvalores',
            'property' => 'listadavezregravalor',
            'service' => 'Nasajon\MDABundle\Service\Crm\ListadavezregrasvaloresService',
            'fixed' => ['tenant','id_grupoempresarial',],
            'constants' => [],
            'values' => ['listadavezregra' =>'listadavezregra'],
        ];
        $builder->add('idlistadavezregravalor', \Nasajon\AppBundle\Type\LookupType::class, $optionsNovo);

        $builder
            ->get('idlistadavezregravalor')
            ->addModelTransformer(new \Symfony\Component\Form\CallbackTransformer(
                function ($data) {
                    if (is_array($data)) {
                        return EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Crm\Listadavezregrasvalores', $data);
                    } else {
                        return $data;
                    }
                },
                function ($data) {
                    return $data;
                }
            ));
    }
}
