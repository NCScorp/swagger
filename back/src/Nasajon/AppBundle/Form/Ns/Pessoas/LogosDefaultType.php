<?php

namespace Nasajon\AppBundle\Form\Ns\Pessoas;

use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Form\Ns\Pessoas\LogosDefaultType as ParentType;

class LogosDefaultType extends ParentType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
    */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('logo', \Symfony\Component\Form\Extension\Core\Type\FileType::class, [
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\NotBlank([
                    'message' => 'É necessário informar a foto da logo'
                ]),
                'file' => new \Symfony\Component\Validator\Constraints\File([
                    'maxSize' => '500k',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png'
                    ],
                    'mimeTypesMessage' => 'O tipo de arquivo enviado não é permitido. Os tipos permitidos são jpeg e png.',
                    'maxSizeMessage' => 'O arquivo enviado é grande demais. Você mandou um arquivo de {{ size }} {{ suffix }} e o máximo permitido é ({{ limit }} {{ suffix }})'
                ])
            ]
        ], $options);
    }
}


