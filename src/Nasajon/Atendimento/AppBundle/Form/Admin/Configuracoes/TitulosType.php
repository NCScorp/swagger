<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin\Configuracoes;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class TitulosType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {


        $builder->add('TITULOS_EXIBIR_PARA_CLIENTE_BOLETO', CheckboxType::class, array(
            'label'    => 'Permitir ao cliente gerar segunda via de boleto',
            'required' => false,
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_ttls_frm_cntrllr.entity.TITULOS_EXIBIR_PARA_CLIENTE_BOLETO',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_ttls_frm_cntrllr.entity.submitting'
            )
        ));
        $builder->add('TITULOS_EXIBIR_PARA_CLIENTE_NOTA_FISCAL', CheckboxType::class, array(
            'label'    => 'Permitir ao cliente gerar segunda via da nota fiscal',
            'required' => false,
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_ttls_frm_cntrllr.entity.TITULOS_EXIBIR_PARA_CLIENTE_NOTA_FISCAL',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_ttls_frm_cntrllr.entity.submitting'
            )
        ));
        $builder->add('TITULOS_EXIBIR_SEGUINTES_SITUACOES', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class , array(
            'label' => 'Cliente visualiza boletos e faturas nas seguintes situações',
            'choices' => [
                'Aberto' => 0,
                'Quitado' => 1,
                'Em débito' => 2,
                'Cancelado' => 3,
                'Protestando' => 4,
                'Estornado' => 5,
                'Agendado' => 6,
                'Suspenso' => 7,
            ],
            'multiple' => true,
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_ttls_frm_cntrllr.entity.TITULOS_EXIBIR_SEGUINTES_SITUACOES',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_ttls_frm_cntrllr.entity.submitting',
                'size'=> 8
            )
        ));
         $builder->add('TITULO_PERIODO_DE_EXIBICAO_PASSADO', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Exibir boletos e faturas com vencimento de ',
            'choices' => [
                'Todos' => 0,
                1 => '1',
                2 => '2',
                3 => '3',
                4 => '4',
                5 => '5',
                6 => '6',
                7 => '7',
                8 => '8',
                9 => '9',
                10 => '10',
                11 => '11',
                12 => '12',
                24 => '24',
                36 => '36',
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_ttls_frm_cntrllr.entity.TITULO_PERIODO_DE_EXIBICAO_PASSADO',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_ttls_frm_cntrllr.entity.submitting'
            )
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'cascade_validation' => true,
            'model' => null,
            'tenant' => null
        ));
    }

}
