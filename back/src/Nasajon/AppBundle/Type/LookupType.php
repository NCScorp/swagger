<?php

namespace Nasajon\AppBundle\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Nasajon\MDABundle\Type\LookupType as ParentLookup;
use Nasajon\AppBundle\Type\Validation\Exist as Exist;

class LookupType extends ParentLookup {

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefault('use_parent_form_entity', false);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $constraints = $options['constraints'];
        $constraints[] = new Exist(['message' => 'Valor { string } não existe no sistema.','service' => $options['service'],
            'fixed' => $options['fixed'],'constants' => $options['constants'], 'values' => $options['values'], 
            'use_parent_form_entity' => $options['use_parent_form_entity']
        ]);
            
        $builder->add($options['property'], null, array(
            'required' => $options['required'],
            'constraints' => $constraints,
            'error_bubbling' => true
        ));   
    }
}
