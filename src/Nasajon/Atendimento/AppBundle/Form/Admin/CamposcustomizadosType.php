<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin;

use Nasajon\MDABundle\Form\Servicos\AtendimentoscamposcustomizadosType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CamposcustomizadosType extends AtendimentoscamposcustomizadosType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('opcoes', CollectionType::class, array(
            'entry_type' => TextType::class,
            'label' => false,
            'allow_extra_fields' => true,
            'allow_add' => true,
            'allow_delete' => true,
        ));
    }

}
