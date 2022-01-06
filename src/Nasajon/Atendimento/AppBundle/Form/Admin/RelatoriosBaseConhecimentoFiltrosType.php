<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RelatoriosBaseConhecimentoFiltrosType extends AbstractType {
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {        
        $builder->add('condicoes', CollectionType::class, array(
            'entry_type' => RelatoriosFiltrosCondicoesType::class,
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
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'cascade_validation' => true
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'relatorios_filter';
    }
}