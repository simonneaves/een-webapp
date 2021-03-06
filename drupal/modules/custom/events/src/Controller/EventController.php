<?php
namespace Drupal\events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Url;
use Drupal\events\Form\EmailVerificationForm;
use Drupal\events\Form\EventForm;
use Drupal\events\Service\EventsService;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class EventController extends ControllerBase
{
    const PAGE_NUMBER = 'page';

    const RESULT_PER_PAGE = 'resultPerPage';
    const DEFAULT_RESULT_PER_PAGE = 20;

    const SEARCH = 'search';

    /**
     * @var PrivateTempStore
     */
    private $session;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;
    /**
     * @var EventsService
     */
    private $service;

    /**
     * EventsController constructor.
     *
     * @param EventsService           $service
     * @param PrivateTempStore        $session
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        EventsService $service,
        PrivateTempStore $session,
        SessionManagerInterface $sessionManager
    )
    {
        $this->service = $service;
        $this->session = $session;
        $this->sessionManager = $sessionManager;

        // TODO check if the user is connected when the login is implemented
        if (!isset($_SESSION['session_started'])) {
            $_SESSION['session_started'] = true;
            $this->sessionManager->start();
        }
    }

    /**
     * @param ContainerInterface $container
     *
     * @return EventController
     */
    public static function create(ContainerInterface $container)
    {
        return new self(
            $container->get('events.service'),
            $container->get('user.private_tempstore')->get('SESSION_ANONYMOUS'),
            $container->get('session_manager')
        );
    }

    /**
     * @param string  $eventId
     * @param string  $token
     * @param Request $request
     *
     * @return array
     */
    public function index($eventId, $token, Request $request)
    {
        $page = $request->query->get(self::PAGE_NUMBER, 1);
        $results = $this->service->get($eventId);

        $form = \Drupal::formBuilder()->getForm(EventForm::class);
        $formEmail = \Drupal::formBuilder()->getForm(EmailVerificationForm::class);
        $formEmail['id']['#value'] = $eventId;

        $this->checkSession($form, $token, $eventId);

        return [
            '#theme'      => 'events_details',
            '#form'       => $form,
            '#form_email' => $formEmail,
            '#event'      => $results,
            '#page'       => $page,
            '#email'      => $this->session->get('email'),
            '#token'      => $token != null && $token === $this->session->get('token'),
            '#route'      => 'events.search',
        ];
    }

    /**
     * @param array  $form
     * @param string $token
     * @param string $eventId
     */
    private function checkSession(&$form, $token, $eventId)
    {
        $emailSession = $this->session->get('email');
        $tokenSession = $this->session->get('token');

        if ($token != null && $token === $tokenSession) {

            $this->clearSession();

            $form['email']['#value'] = $emailSession;
            $form['email']['#attributes']['disabled'] = 'disabled';

            $form['#action'] = Url::fromRoute(
                'events.details',
                [
                    'eventId' => $eventId,
                    'token'   => $token,
                ]
            )->toString();

            $contact = $this->service->createLead($emailSession);

            $this->session->set('isLoggedIn', true);
            $this->session->set('type', $contact['Contact_Status__c']);

            if ($contact['Contact_Status__c'] !== 'Lead') {
                $this->setSession($contact);
            }
        } else {
            $this->disableForm($form);
            $this->session->set('isLoggedIn', false);
        }
    }

    /**
     * Delete all the information stored in session
     */
    private function clearSession()
    {
        $this->session->delete('email-verification');
        $this->session->delete('dietary');
        $this->session->delete('description');
        $this->session->delete('interest');
        $this->session->delete('more');
        $this->session->delete('phone');

        $this->session->delete('step1');
        $this->session->delete('firstname');
        $this->session->delete('lastname');
        $this->session->delete('contact_email');
        $this->session->delete('contact_phone');
        $this->session->delete('newsletter');

        $this->session->delete('step2');
        $this->session->delete('company_name');
        $this->session->delete('company_number');
        $this->session->delete('website');
        $this->session->delete('company_phone');

        $this->session->delete('step3');
        $this->session->delete('postcode');
        $this->session->delete('addressone');
        $this->session->delete('addresstwo');
        $this->session->delete('city');
    }

    /**
     * @param array $contact
     */
    public function setSession($contact)
    {
        $this->session->set('step1', true);
        $this->session->set('firstname', $contact['FirstName']);
        $this->session->set('lastname', $contact['LastName']);
        $this->session->set('contact_email', $contact['Email']);
        $this->session->set('contact_phone', $contact['MobilePhone']);
        if ($contact['Email_Newsletter__c']) {
            $this->session->set('newsletter', $contact['Email_Newsletter__c']);
        }

        $this->session->set('step2', true);
        $this->session->set('company_name', $contact['Account']['Name']);
        $this->session->set('company_number', $contact['Account']['Company_Registration_Number__c']);
        if (isset($contact['Account']['Website'])) {
            $this->session->set('website', $contact['Account']['Website']);
        }
        if (isset($contact['Account']['Phone'])) {
            $this->session->set('company_phone', $contact['Account']['Phone']);
        }

        $this->session->set('step3', true);
        if (isset($contact['MailingPostalCode'])) {
            $this->session->set('postcode', $contact['MailingPostalCode']);
        }
        if (isset($contact['MailingStreet'])) {
            $this->session->set('addressone', $contact['MailingStreet']);
        }
        if (isset($contact['MailingCity'])) {
            $this->session->set('city', $contact['MailingCity']);
        }
    }

    /**
     * @param array $form
     */
    private function disableForm(&$form)
    {
        $form['dietary']['#attributes']['disabled'] = 'disabled';
        $form['actions']['submit']['#attributes']['disabled'] = 'disabled';
    }
}
