<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin;

use Nasajon\MDABundle\Form\Servicos\AtendimentosfilasobservadoresType;
use Nasajon\MDABundle\Form\Servicos\AtendimentosfilasType as AtendimentosfilasParentType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class AtendimentosfilasType extends AtendimentosfilasParentType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('observadores', CollectionType::class, array(
            'entry_type' => AtendimentosfilasobservadoresType::class,
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
        ));
    }

}
