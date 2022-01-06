<?php

namespace Nasajon\Atendimento\AppBundle\Form\Admin;

use Nasajon\MDABundle\Form\Atendimento\Admin\ArtigosType as ArtigosParentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtigosType extends ArtigosParentType {

  /**
   * @param FormBuilderInterface $builder
   * @param array $options
   */
  public function buildForm(FormBuilderInterface $builder, array $options) {
    parent::buildForm($builder, $options);
    if ($options['tag_required']) {
      $builder->add('tags', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, array(
          'entry_type' => \Symfony\Component\Form\Extension\Core\Type\TextType::class,
          'label' => '',
          'allow_add' => true,
          'required' => true,
          'constraints' => [new \Symfony\Component\Validator\Constraints\Count([
                  'minMessage' => 'O campo tag não pode ser vazio.',
                  'min' => 1
                      ])]
      ));
    }
  }

  /**
   * @param OptionsResolverInterface $resolver
   */
  public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefaults(array(
        'data_class' => 'Nasajon\MDABundle\Entity\Atendimento\Admin\Artigos',
        'attr' => ['novalidate' => 'novalidate'],
        'csrf_protection' => false,
        'allow_extra_fields' => true,
        'tag_required' => false
    ));
  }

}
