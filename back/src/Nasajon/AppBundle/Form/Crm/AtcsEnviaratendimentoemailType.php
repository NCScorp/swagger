<?php

namespace Nasajon\AppBundle\Form\Crm;

use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Nasajon\MDABundle\Form\Crm\AtcsEnviaratendimentoemailType as ParentForm;

class AtcsEnviaratendimentoemailType extends ParentForm
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options){
        // Chamo função do pai para montar o form
        parent::buildForm($builder, $options);

        // Documentos a serem gerados e enviados por e-mail
        $builder->add('documentosemail', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, array(
          'label' => 'Lista de documentos',
          'required' => true,
          'error_bubbling' => false,
          'entry_type' => 'Nasajon\MDABundle\Form\Crm\AtcsBaixardocumentoType',
          'allow_add' => true,
          'allow_delete' => false,
          'constraints' => new \Symfony\Component\Validator\Constraints\Valid(),
        ));

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