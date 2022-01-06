<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RelatoriosFiltrosCondicoesType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('campo', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Campo',
            'choices' => array_merge($options['camposcustomizados'], [
                'usuariofollowup' => 'usuariofollowup',
                'representante_tecnico' => 'representante_tecnico',
                'usuario' => 'usuario',
                'fila' => 'fila',
                'cliente' => 'cliente',
                'equipe' => 'equipe',
                'canal' => 'canal',
                'situacao' => 'situacao',
                'categoria' => 'categoria',
                'subcategoria' => 'subcategoria',
                'secao' => 'secao',
                'criador' => 'criador',
                'criado_por_resposta' => 'criado_por_resposta',
                'titulo_artigo' => 'titulo_artigo',
                'cliente_resposta' => 'cliente_resposta',
                'conteudo_resposta' => 'conteudo_resposta',
                'conteudo_resumo' => 'conteudo_resumo',
                'data_criacao' => 'data_criacao',
                'data_atualizacao' => 'data_atualizacao',
                'data_publicacao' => 'data_publicacao'
            ]),
            'required' => true,
            'attr' => array(
                'ng-model' => 'srvcs_tndmntsrgrscndcs_frm_cntrllr.entity.campo',
                'ng-disabled' => 'srvcs_tndmntsrgrscndcs_frm_cntrllr.entity.submitting',
                'placeholder' => '',
            ),
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\NotBlank([
                    'message' => 'Campo não pode ser vazio.',
                        ]),
            ],
        ));
        $builder->add('operador', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'label' => 'Operador',
            'choices' => [
                'is_equal' => 'is_equal',
                'is_not_equal' => 'is_not_equal',
                'is_set' => 'is_set',
                'is_not_set' => 'is_not_set',
                'is_greater' => 'is_greater',
                'is_smaller' => 'is_smaller',
                'is_between' => 'is_between',
                'includes' => 'includes'
            ],
            'required' => true,
            'attr' => array(
                'ng-model' => 'srvcs_tndmntsrgrscndcs_frm_cntrllr.entity.operador',
                'ng-disabled' => 'srvcs_tndmntsrgrscndcs_frm_cntrllr.entity.submitting',
                'placeholder' => '',
            ),
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\NotBlank([
                    'message' => 'Operador não pode ser vazio.',
                        ]),
            ],
        ));
        $builder->add('valor', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'Valor',
            'required' => false,
            'attr' => array(
                'ng-model' => 'srvcs_tndmntsrgrscndcs_frm_cntrllr.entity.valor',
                'ng-disabled' => 'srvcs_tndmntsrgrscndcs_frm_cntrllr.entity.submitting',
                'placeholder' => '',
            ),
        ));
        $builder->add('valor2', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'Valor2',
            'required' => false,
            'attr' => array(
                'ng-model' => 'srvcs_tndmntsrgrscndcs_frm_cntrllr.entity.valor2',
                'ng-disabled' => 'srvcs_tndmntsrgrscndcs_frm_cntrllr.entity.submitting',
                'placeholder' => '',
            ),
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
            'camposcustomizados' => []
        ));
    }

}
