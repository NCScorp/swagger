<?php

namespace Nasajon\AppBundle\Form\Crm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Nasajon\MDABundle\Form\Crm\AtcspendenciasDefaultType as ParentForm;

class AtcspendenciasDefaultType extends ParentForm
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Chamo função do pai para montar o form
        parent::buildForm($builder, $options);

        $builder->remove('dataprazopendencia');
        $builder->add('dataprazopendencia', \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class, array(
            'label' => 'dataprazopendencia',
            'required' => true,
            'error_bubbling' => true,
            'widget' => 'single_text',
            'input' => 'string',
            'format' => 'yyyy-MM-dd H:m:s',
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\NotBlank([
                    'message' => 'O campo data prazo pendencia não pode estar vazio',
                ]),
                new \Symfony\Component\Validator\Constraints\DateTime([
                    'message' => 'O campo data prazo pendencia é inválido',
                ]),
            ],
        ));
    }
}
