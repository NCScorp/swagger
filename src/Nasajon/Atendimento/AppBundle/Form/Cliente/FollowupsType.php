<?php

namespace Nasajon\Atendimento\AppBundle\Form\Cliente;

use Nasajon\MDABundle\Form\Atendimento\Cliente\FollowupsType as FollowupsParentType;
use Nasajon\MDABundle\Form\Ns\DocumentosgedType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class FollowupsType extends FollowupsParentType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('anexos', CollectionType::class, array(
            'entry_type' => DocumentosgedType::class,
            'allow_add' => true,
            'entry_options' => [
                'allow_extra_fields' => true,
            ]
        ));
    }

}
