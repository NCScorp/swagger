<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin\Configuracoes;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomizacoesType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('PORTAL_TITULO', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'Título do Portal',
            'attr' => array(
                'maxlength' => '200',
                'ng-model' => 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr.entity.PORTAL_TITULO',
            )
        ));
        $builder->add('PORTAL_DESCRICAO', \Nasajon\MDABundle\Type\WysiwygType::class, array(
            'controller' => 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr',
            'label' => 'Descrição do Portal',
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr.entity.PORTAL_DESCRICAO',
            )
        ));
        $builder->add('PLACEHOLDER_BUSCA_ARTIGOS_CLIENTE', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'Mensagem do campo de busca',
            'attr' => array(
                'maxlength' => '50',
                'ng-model' => 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr.entity.PLACEHOLDER_BUSCA_ARTIGOS_CLIENTE',
            )
        ));
        $builder->add('TEXTO_BOTAO_BASE_CONHECIMENTO_CLIENTE', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'Botão da base de conhecimento',
            'attr' => array(
                'maxlength' => '50',
                'ng-model' => 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr.entity.TEXTO_BOTAO_BASE_CONHECIMENTO_CLIENTE',
            )
        ));
        $builder->add('ARTIGO_DESCRICAO', \Nasajon\MDABundle\Type\WysiwygType::class, array(
            'controller' => 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr',
            'label' => 'Descrição de Artigos',
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr.entity.ARTIGO_DESCRICAO',
            )
        ));
        $builder->add('CHAMADOS_DESCRICAO', \Nasajon\MDABundle\Type\WysiwygType::class, array(
            'controller' => 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr',
            'label' => 'Descrição de Chamados',
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr.entity.CHAMADOS_DESCRICAO',
            )
        ));
        $builder->add('TITULOS_DESCRICAO', \Nasajon\MDABundle\Type\WysiwygType::class, array(
            'controller' => 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr',
            'label' => 'Descrição de Títulos',
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr.entity.TITULOS_DESCRICAO',
            )
        ));
        $builder->add('DOWNLOADS_DESCRICAO', \Nasajon\MDABundle\Type\WysiwygType::class, array(
            'controller' => 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr',
            'label' => 'Descrição de Downloads',
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr.entity.DOWNLOADS_DESCRICAO',
            )
        ));
        $builder->add('TITULO_ASSUNTO', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'Título do campo assunto',
            'attr' => array(
                'maxlength' => '50',
                'ng-model' => 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr.entity.TITULO_ASSUNTO',
            )
        ));
        $builder->add('PLACEHOLDER_ASSUNTO', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'Mensagem do campo assunto',
            'attr' => array(
                'maxlength' => '50',
                'ng-model' => 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr.entity.PLACEHOLDER_ASSUNTO',
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
