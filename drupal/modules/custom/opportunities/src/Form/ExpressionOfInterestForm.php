<?php
namespace Drupal\opportunities\Form;

use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

class ExpressionOfInterestForm extends AbstractForm
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'expression_of_interest_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = [
            'description' => [
                '#type'                => 'textarea',
                '#title'               => t('Short description of your organisation, activities, products and services'),
                '#field_prefix'        => t('Why do EEN need this?'),
                '#description'         => t('Lorem ipsum'),
                '#description_display' => 'before',
                '#label_display'       => 'before',
                '#attributes'          => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'interest'    => [
                '#type'                => 'textarea',
                '#title'               => t('What interests you about this opportunity and what do you expect of that organisation?'),
                '#field_prefix'        => t('Why do EEN need this?'),
                '#description'         => t('Lorem ipsum'),
                '#description_display' => 'before',
                '#label_display'       => 'before',
                '#attributes'          => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'more'        => [
                '#type'                => 'textarea',
                '#title'               => t('Is there anything further you would like to know about this opportunity?'),
                '#field_prefix'        => t('Why do EEN need this?'),
                '#description'         => t('Lorem ipsum'),
                '#description_display' => 'before',
                '#label_display'       => 'before',
                '#attributes'          => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'email'       => [
                '#type'          => 'textfield',
                '#title'         => t('Email'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'phone'       => [
                '#type'          => 'textfield',
                '#title'         => t('Phone number'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'phoneStatus' => [
                '#type'  => 'hidden',
                '#attributes'    => [
                    'class' => [
                        'phoneStatus',
                    ],
                ],
            ],
            'actions'     => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Express your interest in this opportunity'),
                    '#button_type' => 'primary',
                    '#ajax'        => [
                        'callback' => [$this, 'submitHandler'],
                    ],
                ],
            ],
            '#method'     => Request::METHOD_POST,
        ];
        $form_state->setCached(false);

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitHandler(array &$form, FormStateInterface $form_state)
    {
        $response = parent::generateAjaxError($form, $form_state, ['description', 'interest', 'more', 'email', 'phone']);

        if (!$form_state->getErrors()) {
            $response->addCommand(new OpenModalDialogCommand('Thank you', 'Your expression of interest has been recorded'));
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::checkRequireField($form_state, 'description');
        parent::checkRequireField($form_state, 'interest');
        parent::checkRequireField($form_state, 'more');
        if ($form_state->getValue('phoneStatus') == '1') {
            parent::checkPhoneField($form_state, 'phone');
        } else {
            parent::checkEmailField($form_state, 'email');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // TODO Submit Form to api
        return false;
    }
}