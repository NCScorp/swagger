<?php

namespace Nasajon\AppBundle\Form\Crm;

use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Nasajon\MDABundle\Form\Crm\AtcsBaixardocumentoType as ParentForm;

class AtcsBaixardocumentoType extends ParentForm
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options){
        // Chamo função do pai para montar o form
        parent::buildForm($builder, $options);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'Nasajon\MDABundle\Entity\Crm\Atcs',
            'attr' => ['novalidate' => 'novalidate'],
            'csrf_protection'   => false,
            'allow_extra_fields' => true,
        ));
    }
}
