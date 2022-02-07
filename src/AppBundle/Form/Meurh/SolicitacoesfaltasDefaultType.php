<?php

namespace AppBundle\Form\Meurh;

use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Resources\Constant\TipoJustificativaConstant;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Form\Meurh\SolicitacoesfaltasDefaultType as ParentForm;

/**
 * Sobrescrito para dar suporte a múltiplas datas
 */

class SolicitacoesfaltasDefaultType extends ParentForm
{

  /**
   * @param FormBuilderInterface $builder
   * @param array $options
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {

    $builder->add('justificada', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, array(
      'label' => 'justificada',
      'required' => false,
    ));

    $builder->add('justificativa', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array(
      'label' => 'Justificativa',
      'required' => true,
      'constraints' => [
        new \Symfony\Component\Validator\Constraints\NotBlank([
          'message' => 'Uma falta justificada requer justificativa.',
        ]),
      ],
    ));

    $builder->add('tipojustificativa', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array(
      'label' => 'tipojustificativa',
      'required' => true,
      'constraints' => [
        new \Symfony\Component\Validator\Constraints\NotBlank([
          'message' => 'Uma falta justificada requer justificativa.',
        ]),
      ],
    ));

    $builder->add('datas', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, array(
      'label' => 'Datas',
      'required' => true,
      'allow_add' => true,
      'constraints' => [
        new \Symfony\Component\Validator\Constraints\NotBlank([
          'message' => 'Uma data deve ser selecionada.',
        ]),
      ],
    ));

    $builder->addEventListener(\Symfony\Component\Form\FormEvents::PRE_SUBMIT, function (\Symfony\Component\Form\FormEvent $event) {
      $form = $event->getForm();
      $data = $event->getData();
      $action = $form->getConfig()->getOption('action');
      if (!empty($form->getConfig()->getDataClass())) {
        $entity = EntityHydrator::hydrate($form->getConfig()->getDataClass(),  $data);
      }


      if ($entity->getTipojustificativa() != TipoJustificativaConstant::OUTROS) {
        $form->remove('justificativa');
      }

      $data = $event->getData();
    });
  }
}
