<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin;

use Nasajon\MDABundle\Type\LookupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SolicitacoesAlteraClienteType extends AbstractType{


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cliente', LookupType::class, array(
                    'label' => 'Cliente',
                    'data_class' => 'Nasajon\MDABundle\Entity\Ns\Clientes',
                    'property' => 'cliente',
                    'required' => false,
                    'constraints' => [
                    ],
                    'attr' => [
                        'ng-model' => $options['controller'].'.entity.cliente',
                        'ng-disabled' => $options['controller'].'.submitting',
                        'ng-change' => $options['controller'].'.save()',
                    ],
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
            'cascade_validation' => true,
            'controller' => 'tndmnt_dmn_slctcs_frm_cntrllr'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'nasajon_mdabundle_atendimento_admin_solicitacoes';
    }

}
