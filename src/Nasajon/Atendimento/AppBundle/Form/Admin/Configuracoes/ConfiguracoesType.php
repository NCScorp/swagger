<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin\Configuracoes;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfiguracoesType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('USUARIO_SEM_CLIENTE_CRIAR_CHAMADO', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Usuário sem cliente associado pode criar chamado',
            'choices' => [
                'Sim' => 1,
                'Não' => 0
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.USUARIO_SEM_CLIENTE_CRIAR_CHAMADO',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.submitting'
            )
        ));

        $builder->add('CHAMADOS_VISIVELCLIENTE_PADRAO', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Chamado é visível para o cliente por padrão',
            'choices' => [
                'Sim' => 1,
                'Não' => 0
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.CHAMADOS_VISIVELCLIENTE_PADRAO',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.submitting'
            )
        ));

        // $builder->add('USUARIO_SEM_EQUIPE_PODE_ACESSAR_CHAMADOS_CLIENTES', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        //     'label' => 'Usuário sem equipe pode acessar chamados e clientes',
        //     'choices' => [
        //         'Sim' => 1,
        //         'Não' => 0
        //     ],
        //     'attr' => array(
        //         'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.USUARIO_SEM_EQUIPE_PODE_ACESSAR_CHAMADOS_CLIENTES',
        //         'ng-disabled' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.submitting'
        //     )
        // ));

        $builder->add('HABILITAR_CAMPO_ASSUNTO_NOS_CHAMADOS', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Permitir o cadastro do campo assunto em chamados',
            'choices' => [
                'Não' => 0,
                'Somente para os usuários' => 1,
                'Para clientes e usuários' => 2
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.HABILITAR_CAMPO_ASSUNTO_NOS_CHAMADOS',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.submitting'
            )
        ));

        $builder->add('URL_AVALIACAO_RESPOSTA_CHAMADO', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'URL da Pesquisa de Avaliação para respostas dos chamados',
            'required' => false,
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.URL_AVALIACAO_RESPOSTA_CHAMADO',
            )
        ));

        $builder->add('DISPONIBILIZAR_ARQUIVOS', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Disponibilizar a funcionalidade de Arquivos',
            'choices' => [
                'Sim' => 1,
                'Não' => 0
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.DISPONIBILIZAR_ARQUIVOS',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.submitting'
            )
        ));
        $builder->add('ARQUIVOS_DOWNLOADS_SUPORTE_ATIVO', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Impedir o acesso de clientes bloqueados na área de downloads',
            'choices' => [                
                'Não' => 0,
                'Sim' => 1,
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.ARQUIVOS_DOWNLOADS_SUPORTE_ATIVO',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.submitting'
            )
        ));
        $builder->add('CADASTRAR_CONTATO_SEM_CLIENTE', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Permitir o cadastro de contatos sem cliente vinculado',
            'choices' => [
                'Sim' => 1,
                'Não' => 0
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.CADASTRAR_CONTATO_SEM_CLIENTE',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.submitting'
            )
        ));
        $builder->add('ARTIGO_TAG_OBRIGATORIO', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Campo TAG obrigatório na criação de artigos',
            'choices' => [
                'Sim' => 1,
                'Não' => 0
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.ARTIGO_TAG_OBRIGATORIO',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.submitting'
            )
        ));
        $builder->add('CLIENTE_SITUACAO_SEM_RESTRICAO_LABEL', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'Descrição da situação de clientes não bloqueados',
            'required' => true,
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.CLIENTE_SITUACAO_SEM_RESTRICAO_LABEL',
            )
        ));

        $builder->add('CHAMADO_SINTOMA_LABEL', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'Label do campo sinntoma',
            'required' => true,
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.CHAMADO_SINTOMA_LABEL',
            )
        ));
        $builder->add('CHAMADO_SINTOMA_OBSERVACAO', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'Observação do campo sinntoma',
            'required' => false,
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.CHAMADO_SINTOMA_OBSERVACAO',
            )
        ));
        
        $builder->add('CHAMADO_POR_EMAIL_HTML', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Receber e-mail com HTML',
            'choices' => [
                'Sim' => 1,
                'Não' => 0
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.CHAMADO_POR_EMAIL_HTM',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.submitting'
            )
        ));
        
        $builder->add('ATENDENTE_CRIA_CHAMADO_SEM_CLIENTE', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Atendente pode criar chamado sem cliente',
            'choices' => [
                'Sim' => 1,
                'Não' => 0
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.ATENDENTE_CRIA_CHAMADO_SEM_CLIENTE',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.submitting'
            )
        ));
        
        $builder->add('ENVIA_EMAIL_CRIACAO_CHAMADO_ADMIN', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Envia e-mail na criação do chamado pelo Atendente',
            'choices' => [
                'Sim' => 1,
                'Não' => 0
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.ENVIA_EMAIL_CRIACAO_CHAMADO_ADMIN',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.submitting'
            )
        ));  

        $builder->add('EXIBE_CLIENTE', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Exibe cliente no chamado e menu',
            'choices' => [
                'Sim' => 1,
                'Não' => 0
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.EXIBE_CLIENTE',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.submitting'
            )
        ));

        $builder->add('TORNAR_PUBLICO_ACESSO_BASE_CONHECIMENTO', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Tornar público o acesso à base do conhecimento?',
            'choices' => [
                'Sim' => 1,
                'Não' => 0
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.TORNAR_PUBLICO_ACESSO_BASE_CONHECIMENTO',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.submitting'
            )
        ));

        // Configuração responsável por listar somente os chamados que determinado usuário criou, na visão do cliente
        // 0 - Usuários do mesmo cliente visualizam todos os chamados
        // 1 - Cada usuário visualiza seu próprio chamado, exceto o Administrador
        $builder->add('ACESSO_CHAMADOS_VISAO_CLIENTE', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Acesso aos chamados na visão do cliente',
            'choices' => [
                'Cada usuário visualiza seu próprio chamado, exceto o Administrador' => 1,
                'Usuários do mesmo cliente visualizam todos os chamados' => 0
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.ACESSO_CHAMADOS_VISAO_CLIENTE',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.submitting'
            )
        ));

        $builder->add('HABILITAR_CCO_CHAMADOS', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Habilitar CCO aos chamados',
            'choices' => [
                'Sim' => 1,
                'Não' => 0
            ],
            'attr' => array(
                'ng-model' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.HABILITAR_CCO_CHAMADOS',
                'ng-disabled' => 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr.entity.submitting'
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

    /**
     * @return string
     */
    public function getName() {
        return 'configuracoes';
    }

}
