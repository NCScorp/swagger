<?php

namespace Nasajon\AppBundle\Form\Financas;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Nasajon\MDABundle\Form\Financas\ContasfornecedoresDefaultType as ParentType;

class ContasfornecedoresDefaultType extends ParentType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // Chamo função do pai para montar o form
        parent::buildForm($builder, $options);

        // Removo e adiciono o campo tipoconta, para usar o Type sobrescrito
        $builder->remove('tipoconta');
        $builder->add('tipoconta', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(

            'label' => 'Tipo de Conta',
            'required' => true,
            'error_bubbling' => true,

            'placeholder' => '',
            'choices' => [
                'Conta Corrente' => '1',
                'Conta Poupança' => '2',
            ],
            'invalid_message' => 'O campo Tipo de Conta da listagem de Dados Bancários possui um valor inválido!',

            'constraints' => [
                new \Symfony\Component\Validator\Constraints\NotBlank([
                    'message' => 'O campo Tipo de Conta da listagem de Dados Bancários não pode ser vazio',
                ]),
            ],

        ));
    }

}
