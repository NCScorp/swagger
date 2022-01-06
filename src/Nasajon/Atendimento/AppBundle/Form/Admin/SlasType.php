<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin;

use Nasajon\MDABundle\Form\Atendimento\Admin\SlasType as SlasParentType;
use Nasajon\MDABundle\Form\Atendimento\Admin\SlascondicoesType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;


class SlasType extends SlasParentType {
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('condicoes_in', CollectionType::class, array(
            'entry_type' => SlascondicoesType::class,
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
        ));

        $builder->add('condicoes_ex', CollectionType::class, array(
            'entry_type' => SlascondicoesType::class,
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
        ));

    }
}