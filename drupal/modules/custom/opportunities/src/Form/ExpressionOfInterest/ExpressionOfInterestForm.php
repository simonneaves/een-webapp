<?php
namespace Drupal\opportunities\Form\ExpressionOfInterest;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Form\AbstractForm;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class ExpressionOfInterestForm extends AbstractForm
{
    /**
     * @var PrivateTempStore
     */
    private $session;

    /**
     * ExpressionOfInterestForm constructor.
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
     * @return ExpressionOfInterestForm
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
                '#title'               => $this->t('Short description of your organisation, activities, products and services'),
                '#description'         => $this->t('This is your pitch: remember to include your unique selling points (USP) and why someone would want to do business with you'),
                '#description_display' => 'before',
                '#label_display'       => 'before',
                '#required'            => true,
                '#attributes'          => [
                    'class'       => [
                        'form-control',
                    ],
                    'placeholder' => [
                        '',
                    ],
                ],
            ],
            'interest'    => [
                '#type'                => 'textarea',
                '#title'               => $this->t('What interests you about this opportunity and what do you expect of that organisation?'),
                '#description'         => $this->t('Tell us why you are a good fit for this opportunity, and why you think you\'re the right people for this partnership'),
                '#description_display' => 'before',
                '#label_display'       => 'before',
                '#required'            => true,
                '#attributes'          => [
                    'class'       => [
                        'form-control',
                    ],
                    'placeholder' => [
                        '',
                    ],
                ],
            ],
            'more'        => [
                '#type'                => 'textarea',
                '#title'               => $this->t('Is there anything further you would like to know about this opportunity?'),
                '#description'         => $this->t('If there\'s anything additional, or commercially sensitive you\'d like to know about this opportunity, please let us know'),
                '#description_display' => 'before',
                '#label_display'       => 'before',
                '#attributes'          => [
                    'class'       => [
                        'form-control',
                    ],
                    'placeholder' => [
                        '',
                    ],
                ],
            ],
            'email'       => [
                '#type'          => 'textfield',
                '#title'         => $this->t('Your Email'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'phone'       => [
                '#type'          => 'textfield',
                '#title'         => $this->t('Phone number'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'phoneStatus' => [
                '#type'       => 'hidden',
                '#attributes' => [
                    'class' => [
                        'phoneStatus',
                    ],
                ],
            ],
            'actions'     => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Submit your application'),
                    '#button_type' => 'primary',
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
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::checkRequireField($form_state, 'description', $this->t('A short description of your organisation is required to complete your application.'));
        parent::checkRequireField($form_state, 'interest', $this->t('Details of your interest in this opportunity are required to complete your application.'));
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        if ($this->session->get('type') === 'Client') {
            $form_state->setRedirect(
                'sign-up.review',
                [
                    'id'   => $this->session->get('id'),
                    'type' => 'opportunities',
                ]
            );
        } else {
            $form_state->setRedirect(
                'sign-up.step1',
                [
                    'id'   => $this->session->get('id'),
                    'type' => 'opportunities',
                ]
            );
        }

        $this->session->set('email-verification', true);
        $this->session->set('type', 'opportunities');
        $this->session->set('description', $form_state->getValue('description'));
        $this->session->set('interest', $form_state->getValue('interest'));
        $this->session->set('more', $form_state->getValue('more'));
        $this->session->set('phone', $form_state->getValue('phone'));

        // Set temporary reference number
        $this->session->set('reference_number', random_int(1111, 9999) . '-' . random_int(1111, 9999));
    }
}