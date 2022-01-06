<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin;

use Nasajon\MDABundle\Form\Atendimento\Admin\ArquivosType as ArquivosParentType;
use Nasajon\MDABundle\Form\Atendimento\Admin\ArquivosregrasType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ArquivosType extends ArquivosParentType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('tamanho_multiplicador', ChoiceType::class, array(
            'label' => '&nbsp;',
            'choices' => array(
                'KB' => '1',
                'MB' => '1024',
                'GB' => '1048576',
                'TB' => '1073741824'
            ),
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_rqvs_frm_cntrllr.entity.multiplicador_tamanho'
            )
        ));

        $builder->add('tamanho', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'Tamanho',
            'required' => false,
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_rqvs_frm_cntrllr.entity.tamanho',
                'ng-disabled' => 'tndmnt_dmn_rqvs_frm_cntrllr.entity.submitting',
                'placeholder' => 'Tamanho',
            ),
        ));

        $builder->add('todosclientes', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Situação',
            'required' => false,
            'placeholder' => false,
            'expanded' => '1',
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_rqvs_frm_cntrllr.entity.situacao',
                'ng-disabled' => 'tndmnt_dmn_rqvs_frm_cntrllr.entity.submitting',
                'inline' => '1',
            ),
            'choices' => [
                'Ativo' => '1',
                'Inativo' => '0',
            ],
        ));

        $builder->add('todosclientes', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Regra de Visualização',
            'required' => false,
            'placeholder' => false,
            'expanded' => '1',
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_rqvs_frm_cntrllr.entity.todosclientes',
                'ng-disabled' => 'tndmnt_dmn_rqvs_frm_cntrllr.entity.submitting',
                'inline' => '1',
            ),
            'choices' => [
                'Todos Visualizam' => '1',
                'Apenas clientes que respeitem as condições' => '0',
            ],
        ));

        $builder->add('arquivosregrasin', CollectionType::class, array(
            'entry_type' => ArquivosregrasType::class,
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true
        ));
        $builder->add('arquivosregrasex', CollectionType::class, array(
            'entry_type' => ArquivosregrasType::class,
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true
        ));
    }

}
