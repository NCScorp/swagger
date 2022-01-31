<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/


namespace Nasajon\AppBundle\Form\Crm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PropostasitensBulkType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('propostasitens', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, array(
            
            'label' => 'Propostas Itens',
            'required' => false,
            'error_bubbling' => true,
            'entry_type' => 'Nasajon\MDABundle\Form\Crm\PropostasitensDefaultType',
            'allow_add' => true,
            'allow_delete' => true,
            'constraints' => new \Symfony\Component\Validator\Constraints\Valid()
        ));

        $builder->add('fornecedor', \Nasajon\MDABundle\Type\LookupType::class, array(

            'label' => 'Fornecedor',
            'required' => false,
            'error_bubbling' => true,
            'data_class' => 'Nasajon\MDABundle\Entity\Ns\Fornecedores',
            'property' => 'fornecedor',
            'service' => 'Nasajon\MDABundle\Service\Ns\FornecedoresService',
            'fixed' => ['tenant', 'id_grupoempresarial',],
            'constants' => [],
            'values' => [],

        ));

        $builder->addEventListener(\Symfony\Component\Form\FormEvents::PRE_SUBMIT, function(\Symfony\Component\Form\FormEvent $event){
            $form = $event->getForm();
            $data = $event->getData();
            $action = $form->getConfig()->getOption('action');

            if (isset($data['propostasitens'])) {
                foreach ($data['propostasitens'] as $k => $propostaitem){
                  $data['propostasitens'][$k] = $propostaitem['obj']; //EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Crm\Propostasitens', $propostaitem['obj']);
                  $data['propostasitens'][$k]['propostacapitulo'] = [];
                }
            }

            $event->setData($data);
            $entity = EntityHydrator::hydrate($form->getConfig()->getDataClass(),  $data);   



            // if( !empty($form->getConfig()->getDataClass()) ){
            //     $entity = EntityHydrator::hydrate($form->getConfig()->getDataClass(),  $data);
            // }
        });

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nasajon\AppBundle\Entity\Crm\PropostasitensBulk',
            'attr' => ['novalidate' => 'novalidate'],
            'csrf_protection'   => false,
            'allow_extra_fields' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'crm_propostasitens_default';
    }
}
