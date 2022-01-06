<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin\Configuracoes;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsuariosDisponibilidadesType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('USUARIOSDISP_QUEM_DETERMINA', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Quem determina se o usuário está disponível ou indisponível?',
            'choices' => [
                'Somente usuários Administradores' => 0,
                'Usuários Administradores e o próprio usuário' => 1
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_srs_dspnbldds_frm_cntrllr.entity.USUARIOSDISP_QUEM_DETERMINA',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_srs_dspnbldds_frm_cntrllr.entity.submitting'
            )
        ));

        $builder->add('USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Se o usuário está indisponível, pode criar/alterar/responder chamados?',
            'choices' => [
                'Sim' => 1,
                'Não' => 0
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_srs_dspnbldds_frm_cntrllr.entity.USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_srs_dspnbldds_frm_cntrllr.entity.submitting'
            )
        ));

        $builder->add('USUARIOSDISP_USUARIO_PODE_SER_ATRIBUIDO_AO_CHAMADO', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Se o usuário está indisponível, pode ser atribuído a um chamado?',
            'choices' => [
                'Sim' => 1,
                'Não' => 0
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_srs_dspnbldds_frm_cntrllr.entity.USUARIOSDISP_USUARIO_PODE_SER_ATRIBUIDO_AO_CHAMADO',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_srs_dspnbldds_frm_cntrllr.entity.submitting'
            )
        ));

        $builder->add('USUARIOSDISP_USUARIO_NOTIFICACOES_DE_CHAMADOS', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Quando indisponível, usuário recebe notificações de chamados?',
            'choices' => [
                'Sim' => 1,
                'Não' => 0
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_srs_dspnbldds_frm_cntrllr.entity.USUARIOSDISP_USUARIO_NOTIFICACOES_DE_CHAMADOS',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_srs_dspnbldds_frm_cntrllr.entity.submitting'
            )
        ));

        $builder->add('USUARIOSDISP_ATRIBUICAO_EM_CHAMADO_COM_RESPOSTA', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Quando usuário indisponível e houver resposta em um chamado atribuído a ele, como proceder?',
            'choices' => [
                'Manter atribuição' => 0,
                'Deixar sem atribuição' => 1,
                'Reatribuir para a última Fila em que esteve. Caso esta não exista, manter atribuição' => 2,
                'Reatribuir para a última Fila em que esteve. Caso esta não exista, deixar sem atribuição' => 3,
                'Reatribuir para a última Fila em que esteve. Caso não exista, direcionar à Regras' => 4
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_srs_dspnbldds_frm_cntrllr.entity.USUARIOSDISP_ATRIBUICAO_EM_CHAMADO_COM_RESPOSTA',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_srs_dspnbldds_frm_cntrllr.entity.submitting'
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
