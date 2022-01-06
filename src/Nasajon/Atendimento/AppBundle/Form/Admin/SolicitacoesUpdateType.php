<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin;

use Nasajon\Atendimento\AppBundle\Form\SolicitacoesCamposcustomizadosType;
use Nasajon\MDABundle\Form\Atendimento\Admin\SolicitacoesType as SolicitacoesParentType;
use Nasajon\MDABundle\Type\LookupType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SolicitacoesUpdateType extends SolicitacoesParentType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('email', EmailType::class, array(
                    'label' => 'Email do contato',
                    'required' => false,
                    'constraints' => [
                    ],
                    'attr' => [
                        'ng-model' => $options['controller'] . '.entity.email',
                        'ng-disabled' => $options['controller'] . '.submitting',
                        'ng-change' => $options['controller'] . '.save()',
                    ],
                ))->add('cliente', LookupType::class, array(
                    'label' => 'Cliente',
                    'data_class' => 'Nasajon\MDABundle\Entity\Ns\Clientes',
                    'property' => 'cliente',
                    'required' => false,
                    'constraints' => [
                    ],
                    'attr' => [
                        'ng-model' => $options['controller'] . '.entity.cliente',
                        'ng-disabled' => $options['controller'] . '.submitting',
                        'ng-change' => $options['controller'] . '.save()',
                    ],
                ))
                ->add('camposcustomizados', SolicitacoesCamposcustomizadosType::class, array(
                    'label' => false,
                    'model' => $options['controller'] . '.entity.camposcustomizados',
                    'ng-disabled' => $options['controller'] . '.submitting',
                    'ng-change' => $options['controller'] . '.save()',
                    'destino' => 'admin',
                    'camposcustomizados' => $options['camposcustomizados']
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
            'controller' => 'tndmnt_dmn_slctcs_frm_cntrllr',
            'camposcustomizados' => []
        ));
    }

  
}
