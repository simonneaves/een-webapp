<?php
namespace Drupal\opportunities\Form;

use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/*
 * 
 * TEMP FORM
 * 
 */

class CompaniesHouseForm extends AbstractForm
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'companies_house';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $types = [
            'UK' => t('UK Newsletter'),
            'EE' => t('East of England'),
            'L'  => t('London'),
            'M'  => t('Midlands'),
            'NE' => t('North England'),
            'NI' => t('Northern Ireland'),
            'SE' => t('South East England'),
            'SW' => t('South West England'),
            'W'  => t('Wales'),
        ];
        $radio = [
            'UK' => t('UK Newsletter'),
            'EE' => t('East of England'),
        ];

        $form = [
            'company_name' => [
                '#type'          => 'textfield',
                '#title'         => t('Company Name'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class'       => [
                        'form-control ch_search',
                    ],
                    'placeholder' => [
                        'Your company\'s name',
                    ],
                    'id'          => [
                        'ch_search',
                    ],
                ],
            ],
            'newsletter'   => [
                '#type'    => 'checkboxes',
                '#title'   => t('Please send me emails when there is a new:'),
                '#options' => $types,
            ],

            'radiobutton' => [
                '#type'       => 'radios',
                '#title'      => t('Please send me emails when there is a new:'),
                '#options'    => $radio,
                '#attributes' => [
                    'class' => [
                        'radio-buttons',
                    ],
                ],
            ],

            'firstname' => [
                '#type'          => 'textfield',
                '#title'         => t('Fisrt name'),
                '#label_display' => 'before',
                '#required'      => true,
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],

            'lastname' => [
                '#type'          => 'textfield',
                '#title'         => t('Last name'),
                '#label_display' => 'before',
                '#required'      => true,
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],

            'email' => [
                '#type'          => 'textfield',
                '#title'         => t('Email'),
                '#label_display' => 'before',
                '#required'      => true,
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],

            'phone' => [
                '#type'          => 'textfield',
                '#title'         => t('Contact telephone number'),
                '#label_display' => 'before',
                '#required'      => true,
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],

            'search' => [
                '#type'                     => 'html_tag',
                '#title'                    => t('Search Companies House'),
                '#label_display'            => 'before',
                '#executes_submit_callback' => false,
                '#value'                    => 'Search Companies House',
                '#tag'                      => 'button',
                '#attributes'               => [
                    'class' => [
                        'form-control',
                    ],
                    'id'    => [
                        'ch-search-trigger',
                    ],
                ],
            ],

            'companynumber' => [
                '#type'          => 'textfield',
                '#title'         => t('Company number'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],

            'nocompanynumber' => [
                '#type'  => 'checkbox',
                '#title' => t('I do not have a company number'),
            ],

            'website' => [
                '#type'          => 'textfield',
                '#title'         => t('Website URL'),
                '#label_display' => 'before',
                '#required'      => true,
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],

            'companytel' => [
                '#type'          => 'textfield',
                '#title'         => t('Company telephone number'),
                '#label_display' => 'before',
                '#required'      => true,
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'postcode'   => [
                '#type'          => 'textfield',
                '#title'         => t('Enter your postcode'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'addressone' => [
                '#type'          => 'textfield',
                '#title'         => t('Address Line 1'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],

            'addresstwo' => [
                '#type'          => 'textfield',
                '#title'         => t('Address Line 2'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],

            'city' => [
                '#type'          => 'textfield',
                '#title'         => t('Town/City'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],

            'county' => [
                '#type'          => 'textfield',
                '#title'         => t('County'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],

            'actions' => [
                '#type'       => 'actions',
                '#attributes' => [
                    'id' => [
                        'ch_search_pp',
                    ],
                ],

                'submit' => [
                    '#type'        => 'button',
                    '#value'       => $this->t('Continue'),
                    '#button_type' => 'primary',
                    '#method'      => 'append',
                    '#url'         => '/opportunities-tempajax',
                    '#ajax'        => [
                        'callback' => 'Drupal\opportunities\Controller\OpportunitiesController::tempajax',
                    ],
                ],
            ],
            '#method' => Request::METHOD_POST,
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
        parent::checkRequireField($form_state, 'description', t('A short description of your organisation is required to complete your application.'));
        parent::checkRequireField($form_state, 'interest', t('Details of your interest in this opportunity are required to complete your application.'));
        parent::checkEmailAndPhoneField($form_state);
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
