<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RelatoriosFiltrosType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $camposcustomizados = [];
        foreach ($options['camposcustomizados'] as $campocustomizado) {
            $camposcustomizados[$campocustomizado['atendimentocampocustomizado']] = $campocustomizado['atendimentocampocustomizado'];
        }

        $builder->add('datainicial', DateType::class, array(
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'input' => 'string',
            'required' => true,
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\NotBlank([
                    'message' => 'Campo não pode ser vazio.',
                        ]),
            ],
        ));
        $builder->add('datafinal', DateType::class, array(
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'input' => 'string',
            'required' => true,
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\NotBlank([
                    'message' => 'Campo não pode ser vazio.',
                        ]),
            ],
        ));
        $builder->add('campocustomizado', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'required' => true,
            'choices' => $camposcustomizados
        ));
        $builder->add('condicoes', CollectionType::class, array(
            'entry_type' => RelatoriosFiltrosCondicoesType::class,
            'allow_add' => true,
            'entry_options' => [
                'allow_extra_fields' => true,
                "camposcustomizados" => $camposcustomizados
            ]
        ));
        
        $builder->add('relatorio', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'required' => true
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
            "camposcustomizados" => []
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'relatorios_filter';
    }

}
