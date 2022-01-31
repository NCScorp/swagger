<?php

namespace Nasajon\AppBundle\Form\Crm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Nasajon\MDABundle\Form\Crm\AtcsDefaultType as ParentType;

class AtcsDefaultType extends ParentType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
    */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Chamo função do pai para montar o form
        parent::buildForm($builder, $options);

        // Removo e adiciono objectlist de Negociosdadosseguradoras, para apontar para o Type sobrescrito
        $builder->remove('negociosdadosseguradoras');
        $builder->add('negociosdadosseguradoras', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, array(
            'label' => 'Dados Seguradora Atendimento comercial',
            'required' => false,
            'error_bubbling' => true,
            'entry_type' => 'Nasajon\AppBundle\Form\Crm\AtcsdadosseguradorasDefaultType',
            'allow_add' => true,
            'allow_delete' => true,
            'constraints' => new \Symfony\Component\Validator\Constraints\Valid(),
        ));
    }
}


