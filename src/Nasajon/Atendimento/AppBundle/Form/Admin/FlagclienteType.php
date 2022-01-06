<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin;

use Nasajon\MDABundle\Form\Atendimento\Admin\FlagclientecondicaoType;
use Nasajon\MDABundle\Form\Atendimento\Admin\FlagclienteType as ParentType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class FlagclienteType extends ParentType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('condicoes_in', CollectionType::class, array(
            'entry_type' => FlagclientecondicaoType::class,
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
        ));

        $builder->add('condicoes_ex', CollectionType::class, array(
            'entry_type' => FlagclientecondicaoType::class,
            'label' => false,           
            'allow_add' => true,
            'allow_delete' => true,
        ));
    }

}
