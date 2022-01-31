<?php

namespace Nasajon\AppBundle\Form\Crm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResponsabilidadesfinanceirasEmLoteDefaultType extends AbstractType
{

  /**
   * @param FormBuilderInterface $builder
   * @param array $options
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('responsabilidadesfinanceiras', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, array(
      'label' => 'Lista de responsabilidades financeiras',
      'required' => true,
      'error_bubbling' => false,
      'entry_type' => 'Nasajon\MDABundle\Form\Crm\ResponsabilidadesfinanceirasDefaultType',
      'allow_add' => true,
      'allow_delete' => false,
      'constraints' => new \Symfony\Component\Validator\Constraints\Valid(),
    ));


    $builder->addEventListener(\Symfony\Component\Form\FormEvents::PRE_SUBMIT, function (\Symfony\Component\Form\FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();
        $action = $form->getConfig()->getOption('action');

        if( !empty($form->getConfig()->getDataClass()) ){
            $entity = EntityHydrator::hydrate($form->getConfig()->getDataClass(),  $data);
        }
    });
  }

  /**
   * @param OptionsResolverInterface $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Nasajon\AppBundle\Entity\Crm\ResponsabilidadesFinanceirasEmLote',
      'attr' => ['novalidate' => 'novalidate'],
      'csrf_protection'   => false,
      'allow_extra_fields' => true,
    ));
  }
}
