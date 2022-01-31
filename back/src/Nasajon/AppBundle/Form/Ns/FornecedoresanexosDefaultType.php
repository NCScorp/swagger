<?php

namespace Nasajon\AppBundle\Form\Ns;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Nasajon\MDABundle\Form\Ns\FornecedoresanexosDefaultType as ParentType;


class FornecedoresanexosDefaultType extends ParentType
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
                'file' => new \Symfony\Component\Validator\Constraints\File(
                )
            ]
        ], $options);

    }

}
