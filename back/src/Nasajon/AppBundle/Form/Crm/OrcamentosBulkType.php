<?php

namespace Nasajon\AppBundle\Form\Crm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrcamentosBulkType extends AbstractType
{

  /**
   * @param FormBuilderInterface $builder
   * @param array $options
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('orcamentos', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, array(
      'label' => 'Lista de Orçamentos',
      'required' => false,
      'error_bubbling' => true,
      'entry_type' => 'Nasajon\MDABundle\Form\Crm\OrcamentosDefaultType',
      'allow_add' => true,
      'allow_delete' => false,
      'constraints' => new \Symfony\Component\Validator\Constraints\Valid()
    ));

    $builder->add('negocio', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
      'label' => '',
      'required' => false,
      'error_bubbling' => true,
    ));

    $builder->add('proposta', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
      'label' => '',
      'required' => false,
      'error_bubbling' => true,
    ));

    $builder->addEventListener(\Symfony\Component\Form\FormEvents::PRE_SUBMIT, function (\Symfony\Component\Form\FormEvent $event) {
      $form = $event->getForm();
      $data = $event->getData();
      $action = $form->getConfig()->getOption('action');
      if (!empty($form->getConfig()->getDataClass())) {
        if (isset($data['orcamentos'])) {
          foreach ($data['orcamentos'] as $k => $orcamento) {
            $data['orcamentos'][$k] = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Crm\Orcamentos', $orcamento);
            // $data['orcamentos'][$k]->setProposta($orcamento['propostaitem']['proposta']);
              // if($orcamento['propostaitem'] && $orcamento['propostaitem']['proposta']){
              //   $data['proposta'] = $orcamento['propostaitem']['proposta'];
              //   $data['negocio'] = $orcamento['propostaitem']['negocio'];
              // }
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
      'data_class' => 'Nasajon\AppBundle\Entity\Crm\OrcamentosBulk',
      'attr' => ['novalidate' => 'novalidate'],
      'csrf_protection'   => false,
      'allow_extra_fields' => true,
    ));
  }
}
