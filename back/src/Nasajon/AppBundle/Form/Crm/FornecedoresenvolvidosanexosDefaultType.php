<?php

namespace Nasajon\AppBundle\Form\Crm;

use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Form\Crm\FornecedoresenvolvidosanexosDefaultType as ParentType;

class FornecedoresenvolvidosanexosDefaultType extends ParentType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
    */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('anexo', \Symfony\Component\Form\Extension\Core\Type\FileType::class, [
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\NotBlank([
                    'message' => 'É necessário enviar o anexo'
                ]),
                'file' => new \Symfony\Component\Validator\Constraints\File([])
            ]
        ], $options);
    }
}


