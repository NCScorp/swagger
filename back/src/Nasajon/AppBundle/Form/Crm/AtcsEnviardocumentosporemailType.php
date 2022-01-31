<?php

namespace Nasajon\AppBundle\Form\Crm;

use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Form\Crm\AtcsEnviardocumentosporemailType as ParentForm;

class AtcsEnviardocumentosporemailType extends ParentForm
{
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
          */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Chamo função do pai para montar o form
        parent::buildForm($builder, $options);

        // Lista de e-mails
        $builder->add('listaemails', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, array(
            'label' => 'Lista de e-mails',
            'required' => true,
            'error_bubbling' => true,
            'entry_type' => \Symfony\Component\Form\Extension\Core\Type\EmailType::class,
            'entry_options' => ['label' => false],
            'allow_add' => true,
            'allow_delete' => false,
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\NotBlank([
                    'message' => 'O campo lista de e-mails não pode estar vazio',
                ]),
            ],
        ));
    }
}


