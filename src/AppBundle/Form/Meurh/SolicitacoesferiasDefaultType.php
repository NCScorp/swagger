<?php


namespace AppBundle\Form\Meurh;

use Symfony\Component\Form\FormBuilderInterface;
use Nasajon\MDABundle\Form\Meurh\SolicitacoesferiasDefaultType as ParentForm;

class SolicitacoesferiasDefaultType extends ParentForm
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('datainiciogozo');
        $builder->add('datainiciogozo', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
            'label' => 'Data Inicio Gozo',
            'required' => true,
            'widget' => 'single_text',
            'input' => 'string',
            'format' => 'yyyy-MM-dd',
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\NotBlank([
                    'message' => 'A data de início de gozo não pode ser vazia',
                ]),
                new \AppBundle\Validator\Constraints\PeriodoAquisitivo([
                    'message' => 'Você pode criar rascunhos, mas não pode enviar marcações nesse período, pois você possui períodos aquisitivos anteriores com pendências de envios.'
                ]),
                new \AppBundle\Validator\Constraints\Marcacao([
                    'message' => 'Erro na marcação da solicitação',
                ]),
            ],
        ));
        $builder->remove('diasferiascoletivas');
        $builder->add('diasferiascoletivas', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'Dias Férias Coletivas',
            'required' => true,
            'constraints' => [
                new \AppBundle\Validator\Constraints\Saldo([
                    'message' => 'Erro na marcação da solicitação',
                ]),
            ],
        ));
        $builder->remove('diasvendidos');
        $builder->add('diasvendidos', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'Dias Vendidos',
            'required' => false,
            'constraints' => [
                new \AppBundle\Validator\Constraints\DiasVendidos([
                    'message' => 'Erro na marcação da solicitação',
                ]),
            ],
        ));  
        $builder->addEventListener(\Symfony\Component\Form\FormEvents::POST_SUBMIT, function (\Symfony\Component\Form\FormEvent $event) {
            $form = $event->getForm();

            if ($form['diasvendidos']->getData() == 0 &&  $form['diasferiascoletivas']->getData() == 0 ) {
                $form['diasvendidos']->addError(new \Symfony\Component\Form\FormError('Não é possível ter uma solicitação sem dias de férias marcados e sem dias vendidos'));
            }

            if ($form['dataaviso']->getData() > $form['datainiciogozo']->getData()) {
                $form['dataaviso']->addError(new \Symfony\Component\Form\FormError('A data de aviso não pode ser posterior a data de início de gozo'));
            }
            if ($form['datainiciogozo']->getData() > $form['datafimgozo']->getData()) {
                $form['datainiciogozo']->addError(new \Symfony\Component\Form\FormError('A data de início de gozo não pode ser posterior a data de fim de gozo'));
            }
        });
    }
}