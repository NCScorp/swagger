<?php

namespace Nasajon\Atendimento\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SolicitacoesCamposcustomizadosType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        foreach ($options['camposcustomizados'] as $campo) {
            $fieldOptions = array(
                'label' => $campo['label'],
                'required' => $campo['obrigatorio'],
                'attr' => [
                    'ng-model' => $options['model'] . '[\'' . $campo['atendimentocampocustomizado'] . '\']',
                    'ng-disabled' => $options['ng-disabled'],
                    'ng-change' => $options['ng-change'],
                ]
            );
            if ($options['destino'] == 'admin' || $campo['exibidoformcontato']) {
                switch ($campo['tipo']) {
                    case "TE":
                        $builder->add($campo['atendimentocampocustomizado'], TextType::class, $fieldOptions);
                        break;
                    case "CB":

                        $opcoes = [];
                        foreach ($campo['opcoes'] as $opt) {
                            $opcoes[$opt] = $opt;
                        }
                        $builder->add($campo['atendimentocampocustomizado'], ChoiceType::class, array_merge($fieldOptions, array(
                            'choices' => $opcoes,
                            'placeholder' => 'Selecione',
                        )));
                        break;
                }
            }
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'cascade_validation' => true,
            'model' => NULL,
            'ng-disabled' => NULL,
            'ng-change' => NULL,
            'destino' => 'cliente',
            'camposcustomizados' => []
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'campos_customizados';
    }

}
