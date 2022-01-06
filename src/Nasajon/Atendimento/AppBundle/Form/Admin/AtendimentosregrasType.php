<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin;

use Nasajon\MDABundle\Form\Servicos\AtendimentosregrasacoesType;
use Nasajon\MDABundle\Form\Servicos\AtendimentosregrascondicoesType;
use Nasajon\MDABundle\Form\Servicos\AtendimentosregrasType as AtendimentosregrasParentType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class AtendimentosregrasType extends AtendimentosregrasParentType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('condicoes_in', CollectionType::class, array(
            'entry_type' => AtendimentosregrascondicoesType::class,
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
        ));

        $builder->add('condicoes_ex', CollectionType::class, array(
            'entry_type' => AtendimentosregrascondicoesType::class,
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
        ));

        $builder->add('acoes', CollectionType::class, array(
            'entry_type' => AtendimentosregrasacoesType::class,
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
        ));
    }

}
