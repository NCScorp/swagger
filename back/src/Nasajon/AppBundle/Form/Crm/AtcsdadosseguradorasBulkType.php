<?php

namespace Nasajon\AppBundle\Form\Crm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AtcsdadosseguradorasBulkType extends AbstractType
{
  
  /**
  * @param FormBuilderInterface $builder
  * @param array $options
  */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
      $builder->add('negociosdadosseguradoras', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, array(
            
            'label' => 'Lista de Negócios Dados Seguradoras',
            'required' => false,
            'error_bubbling' => true,
            'entry_type' => 'Nasajon\MDABundle\Form\Crm\AtcsdadosseguradorasDefaultType',
            'allow_add' => true,
            'allow_delete' => true,
            'constraints' => new \Symfony\Component\Validator\Constraints\Valid()
      ));
      $builder->addEventListener(\Symfony\Component\Form\FormEvents::PRE_SUBMIT, function(\Symfony\Component\Form\FormEvent $event){
          $form = $event->getForm();
          $data = $event->getData();
          $action = $form->getConfig()->getOption('action');
          if( !empty($form->getConfig()->getDataClass()) ){
            if (isset($data['negociosdadosseguradoras'])) {
              foreach ($data['negociosdadosseguradoras'] as $k => $negociosdadosseguradoras){
                $data['negociosdadosseguradoras'][$k] = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Crm\Atcsdadosseguradoras', $negociosdadosseguradoras);
              }
            } 
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
      'data_class' => 'Nasajon\AppBundle\Entity\Crm\AtcsdadosseguradorasBulk',
      'attr' => ['novalidate' => 'novalidate'],
      'csrf_protection'   => false,
      'allow_extra_fields' => true,
    ));
  }
  
  
}
                
                
