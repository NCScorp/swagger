<?php
/*
 * Sobrescrito para tratar conteúdo
 */

namespace AppBundle\Form\Meurh;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SolicitacoesdocumentosDefaultType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('solicitacao', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(

            'label' => 'Solicitação',
            'required' => true,
            'error_bubbling' => true,
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\NotBlank([
                    'message' => 'O campo solicitação não pode ser vazio.',
                ]),
            ],
        ));

        $builder->add('conteudo', \Symfony\Component\Form\Extension\Core\Type\FileType::class, array(
            'label' => 'Conteúdo',
            'required' => true,
            'constraints' => [
                new File([
                    'mimeTypes' => [
                        'application/pdf',
                        'application/x-pdf',
                        'image/png',
                        'image/jpeg',
                        'image/jpg',
                        'image/gif'
                    ],
                    'mimeTypesMessage' => 'O campo Conteúdo não pode ser vazio',
                ]),
            ],
        ));

        $builder->add('tipodocumentocolaborador', \Nasajon\MDABundle\Type\LookupType::class, array(
            'label' => 'Tipo de Documento do Colaborador',
            'required' => true,
            'error_bubbling' => true,
            'data_class' => 'Nasajon\MDABundle\Entity\Persona\Tiposdocumentoscolaboradores',
            'property' => 'tipodocumentocolaborador',
            'service' => 'Nasajon\MDABundle\Service\Persona\TiposdocumentoscolaboradoresService',
            'fixed' => ['tenant',],
            'constants' => [],
            'values' => [],
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\NotBlank([
                    'message' => 'O campo Tipo de Documento do Colaborador não pode ser vazio.',
                ]),
            ],
        ));
        
        $builder
        ->get('tipodocumentocolaborador')
        ->addModelTransformer(new \Symfony\Component\Form\CallbackTransformer(
            function ($data) {
                if (is_array($data)) {
                    return EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Persona\Tiposdocumentoscolaboradores', $data);
                } else {
                    return $data;
                }
            },
            function ($data) {
                return $data;
            }
        ));

        $builder->add('solicitacaohistorico', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'Histórico de Solicitação',
            'required' => false,
        ));   

        $builder->addEventListener(\Symfony\Component\Form\FormEvents::PRE_SUBMIT, function (\Symfony\Component\Form\FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            $action = $form->getConfig()->getOption('action');
            if (isset($data['tipodocumentocolaborador']) && is_array($data['tipodocumentocolaborador'])) {
                $data['tipodocumentocolaborador'] = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Persona\Tiposdocumentoscolaboradores', $data['tipodocumentocolaborador']);
            }
            if (!empty($form->getConfig()->getDataClass())) {
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
            'data_class' => 'Nasajon\MDABundle\Entity\Meurh\Solicitacoesdocumentos',
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
        return 'meurh_solicitacoesdocumentos_default';
    }
}
