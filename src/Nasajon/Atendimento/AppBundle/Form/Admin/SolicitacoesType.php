<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin;

use Nasajon\Atendimento\AppBundle\Form\SolicitacoesCamposcustomizadosType;
use Nasajon\MDABundle\Form\Atendimento\Admin\SolicitacoesType as SolicitacoesParentType;
use Nasajon\MDABundle\Form\Ns\DocumentosgedType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SolicitacoesType extends SolicitacoesParentType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('camposcustomizados', SolicitacoesCamposcustomizadosType::class, array(
            'label' => false,
            'model' => 'tndmnt_dmn_slctcs_frm_cntrllr.entity.camposcustomizados',
            'ng-disabled' => 'tndmnt_dmn_slctcs_frm_cntrllr.submitting',
            'destino' => 'admin',
            'camposcustomizados' => $options['camposcustomizados']
        ));

        $builder->add('anexos', CollectionType::class, array(
            'entry_type' => DocumentosgedType::class,
            'allow_add' => true,
            'entry_options' => [
                'allow_extra_fields' => true
            ]
        ));
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes',
            'attr' => ['novalidate' => 'novalidate'],
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'controller' => null,
            'camposcustomizados' => []
        ));
    }

}
