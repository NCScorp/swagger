<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin\Configuracoes;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TermoType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
      
      $builder->add('TERMO_HABILITADO', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
          'label' => 'Habilita termo de serviço',
          'choices' => [
              'Sim' => 1,
              'Não' => 0
          ],
          'attr' => array(
              'ng-model' => 'tndmnt_dmn_cnfgrcs_trm_frm_cntrllr.entity.TERMO_HABILITADO',
              'ng-disabled' => 'tndmnt_dmn_cnfgrcs_trm_frm_cntrllr.entity.submitting'
          )
      ));
      
      $builder->add('TERMO_TEXTO', \Nasajon\MDABundle\Type\WysiwygType::class, array(
          'controller' => 'tndmnt_dmn_cnfgrcs_trm_frm_cntrllr',
          'label' => 'Termo texto',
          'required' => true,
          'attr' => array(
              'ng-model' => 'tndmnt_dmn_cstmzcs_trm_frm_cntrllr.entity.TERMO_TEXTO',
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
