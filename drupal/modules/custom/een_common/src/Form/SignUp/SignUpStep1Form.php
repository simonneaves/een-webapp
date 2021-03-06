<?php
namespace Drupal\een_common\Form\SignUp;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Form\AbstractForm;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class SignUpStep1Form extends AbstractForm
{
    /**
     * @var PrivateTempStore
     */
    private $session;

    /**
     * SignUpStep1Form constructor.
     *
     * @param PrivateTempStore $session
     */
    public function __construct(PrivateTempStore $session)
    {
        $this->session = $session;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return SignUpStep1Form
     */
    public static function create(ContainerInterface $container)
    {
        return new self($container->get('user.private_tempstore')->get('SESSION_ANONYMOUS'));
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'sign_up_step_1_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $types = [
            'BP' => t('Blog Posts'),
            'C'  => t('Consultations on EU law'),
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
            'firstname'     => [
                '#type'           => 'textfield',
                '#title'          => t('First name'),
                '#label_display'  => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-firstname',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The first name is required to complete your application.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                    'autocomplete' => [
                        'given-name',
                    ],
                ],

            ],
            'lastname'      => [
                '#type'           => 'textfield',
                '#title'          => t('Last name'),
                '#label_display'  => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-lastname',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The last name is required to complete your application.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                    'autocomplete' => [
                        'family-name',
                    ],
                ],
            ],
            'contact_email' => [
                '#type'           => 'email',
                '#title'          => t('Email'),
                '#label_display'  => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-contact-email',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The email is required to complete your application.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                    'autocomplete' => [
                        'email',
                    ],
                ],
            ],
            'contact_phone' => [
                '#type'           => 'number',
                '#title'          => t('Contact telephone number'),
                '#label_display'  => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-contact-phone',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The telephone number is required to complete your application.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                    'autocomplete' => [
                        'tel',
                    ],
                ],
            ],
            'newsletter'    => [
                '#type'    => 'checkboxes',
                '#title'   => t('<span tabindex="0">Please send me emails when there is a new:</span>'),
                '#options' => $types,
            ],

            'actions' => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Continue'),
                    '#button_type' => 'primary',
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
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        if (!parent::checkRegexField($form_state, self::EMAIL_REGEX, 'contact_email')) {
            $form_state->setErrorByName(
                'contact_email',
                [
                    'key'          => 'edit-contact-email',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The email is required to complete your application.'),
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $form_state->setRedirect(
            'sign-up.step2',
            [
                'id'   => $this->session->get('id'),
                'type' => $this->session->get('type'),
            ]
        );

        $this->session->set('step1', true);
        $this->session->set('firstname', $form_state->getValue('firstname'));
        $this->session->set('lastname', $form_state->getValue('lastname'));
        $this->session->set('contact_email', $form_state->getValue('contact_email'));
        $this->session->set('contact_phone', $form_state->getValue('contact_phone'));
        $this->session->set('newsletter', $this->purgeValues($form_state->getValue('newsletter')));
        $this->session->set('radiobutton', $this->purgeValues($form_state->getValue('newsletter')));
    }

    private function purgeValues($values)
    {
        return array_filter($values, function($value) {
            return !empty($value);
        });
    }
}