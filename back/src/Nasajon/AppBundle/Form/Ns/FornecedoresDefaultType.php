<?php

namespace Nasajon\AppBundle\Form\Ns;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Nasajon\MDABundle\Form\Ns\FornecedoresDefaultType as ParentType;

class FornecedoresDefaultType extends ParentType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // Chamo função do pai para montar o form
        parent::buildForm($builder, $options);
        
        // Removo e adiciono o campo dados bancarios, para usar o form sobrescrito de contas fornecedores
        $builder->remove('dadosbancarios');
        $builder->add('dadosbancarios', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, array(

            'label' => 'Dados Bancarios',
            'required' => false,
            'error_bubbling' => true,
            'entry_type' => 'Nasajon\AppBundle\Form\Financas\ContasfornecedoresDefaultType',
            'allow_add' => true,
            'allow_delete' => true,
            'constraints' => new \Symfony\Component\Validator\Constraints\Valid(),

        ));

    }

}
