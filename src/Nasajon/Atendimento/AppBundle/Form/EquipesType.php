<?php

namespace Nasajon\Atendimento\AppBundle\Form;

use Nasajon\MDABundle\Form\Atendimento\EquipesType as EquipesParentType;
use Nasajon\MDABundle\Form\Atendimento\EquipesusuariosType;
use Nasajon\MDABundle\Form\Atendimento\EquipesclientesregrasType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class EquipesType extends EquipesParentType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('todosclientes', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Clientes',
            'required' => false,
            'placeholder' => false,
            'expanded' => '1',
            'attr' => array(
                'ng-model' => 'tndmnt_qps_frm_cntrllr.entity.todosclientes',
                'ng-disabled' => 'tndmnt_qps_frm_cntrllr.entity.submitting',
                'inline' => '1',
            ),
            'choices' => [
                'Todos' => '1' ,
                'Configurações Avançadas' => '0',
            ],
        ));

        $builder->add('usuarios', CollectionType::class, array(
            'entry_type' => EquipesusuariosType::class,
            'label' => false,            
            'allow_add' => true,
            'allow_delete' => true
        ));

        $builder->add('clientesregrasin', CollectionType::class, array(
            'entry_type' => EquipesclientesregrasType::class,
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true
        ));
        $builder->add('clientesregrasex', CollectionType::class, array(
            'entry_type' => EquipesclientesregrasType::class,
            'label' => false,            
            'allow_add' => true,
            'allow_delete' => true
        ));
    }

}
