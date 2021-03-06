<?php

namespace Drupal\opportunities\Service;

use Drupal\Core\Url;
use Drupal\service_connection\Service\HttpService;
use Drupal\opportunities\Form\OpportunitiesForm;
use Symfony\Component\HttpFoundation\Request;

class OpportunitiesService
{
    const PAGE_NUMBER = 'page';
    const RESULT_PER_PAGE = 'resultPerPage';
    const DEFAULT_RESULT_PER_PAGE = 20;
    const SEARCH = 'search';
    const OPPORTUNITY_TYPE = 'opportunity_type';
    const COUNTRY = 'country';

    /**
     * @var HttpService
     */
    private $service;

    /**
     * OpportunitiesController constructor.
     *
     * @param HttpService $service
     */
    public function __construct(HttpService $service)
    {
        $this->service = $service;
    }

    /**
     * @param string $search
     * @param array  $types
     * @param array  $countries
     *
     * @return array
     */
    public function count($search, $types, $countries)
    {
        $params = [
            'search'           => $search,
            'opportunity_type' => $types,
            'country'          => $countries,
            'count'            => true,
        ];

        return $this->service->execute(Request::METHOD_POST, 'opportunities', $params);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getOpportunities(Request $request, $resultsPerPage = self::DEFAULT_RESULT_PER_PAGE)
    {
        $form = \Drupal::formBuilder()->getForm(OpportunitiesForm::class);

        $page = $request->query->get(self::PAGE_NUMBER, 1);
        $resultPerPage = $request->query->get(self::RESULT_PER_PAGE, $resultsPerPage);
        $search = $request->query->get(self::SEARCH);
        $types = $request->query->get(self::OPPORTUNITY_TYPE);
        $countries = $request->query->get(self::COUNTRY);

        if (is_array($countries) && current($countries) == 'anywhere') {
            $countries = array_keys($this->getCountryList());
        }
        if (is_array($countries) && current($countries) == 'europe') {
            $countries = $this->getEuropeCountryCode();
        }

        $results = $this->search($form, $search, $types, $countries, $page, $resultPerPage, 3);

        // Test if single match
        if (isset($results['_id'])) {
            return [
                'redirect' => true,
                'id'       => $results['_id'],
            ];
        }

        return [
            'form'             => $form,
            'results'          => $results['results'],
            'total'            => $results['total'],
            'pageTotal'        => (int)ceil($results['total'] / $resultPerPage),
            'page'             => $page,
            'resultPerPage'    => $resultPerPage,
            'search'           => $search,
            'opportunity_type' => $types,
            'country'          => $countries,
        ];
    }

    /**
     * @return array
     */
    public function getEuropeCountryCode()
    {
        return [
            'AD', 'AL', 'AT', 'BA', 'BE', 'BG', 'BY', 'CH', 'CY', 'CZ',
            'DE', 'DK', 'EE', 'ES', 'FI', 'FO', 'FR', 'GB', 'GI', 'GR',
            'HR', 'HU', 'IE', 'IM', 'IS', 'IT', 'LI', 'LT', 'LU', 'LV',
            'MC', 'MD', 'ME', 'MK', 'MT', 'NL', 'NO', 'PL', 'PT', 'RO',
            'RS', 'RU', 'SE', 'SI', 'SK', 'SM', 'UA', 'UK', 'VA',
        ];
    }

    /**
     * @param array  $form
     * @param string $search
     * @param array  $types
     * @param array  $countries
     * @param int    $page
     * @param int    $resultPerPage
     * @param int    $searchType
     *
     * @return array|null
     */
    public function search(&$form, $search, $types, $countries, $page, $resultPerPage, $searchType = 1)
    {
        $form['search']['#value'] = $search;
        $this->addCheckboxAttributes($form, $types, 'opportunity_type');
        $this->addCheckboxAttributes($form, $countries, 'country');

        $params = [
            'type'             => $searchType,
            'from'             => ($page - 1) * $resultPerPage,
            'size'             => $resultPerPage,
            'search'           => $search,
            'opportunity_type' => $types,
            'country'          => $countries,
            'source'           => ['type', 'title', 'summary', 'date', 'country', 'country_code'],
        ];

        if (empty($search)) {
            $params['sort'] = [
                'date' => ['order' => 'desc'],
            ];
        }

        $results = $this->service->execute(Request::METHOD_POST, 'opportunities', $params);

        if (array_key_exists('error', $results)) {
            if (is_array($results['error'])) {
                foreach ($results['error'] as $key => $error) {
                    drupal_set_message($key . ' => ' . array_pop($error), 'error');
                }
            } else {
                drupal_set_message($results['error'], 'error');
            }
            $results = null;
        }

        if (!isset($results['_id'])) {
            return $this->reformatResults($results);
        }

        return $results;
    }

    /**
     * @param array  $form
     * @param array  $fields
     * @param string $name
     */
    private function addCheckboxAttributes(&$form, $fields, $name)
    {
        if (empty($fields) === false) {
            foreach ($fields as $field) {
                $form[$name][$field]['#attributes']['checked'] = 'checked';
            }
        }
    }

    /**
     * @param array $results
     *
     * @return array
     */
    private function reformatResults($results)
    {
        $response = [
            'total'   => $results['total'],
            'results' => [],
        ];

        if (isset($results['results']) === false) {
            return $response;
        }

        foreach ($results['results'] as $result) {

            $title = isset($result['highlight']['title']) ? implode('', $result['highlight']['title']) : $result['_source']['title'];
            $summary = (isset($result['highlight']['summary'])
                ? implode('', $result['highlight']['summary'])
                : (isset($result['highlight']['description'])
                    ? implode('', $result['highlight']['description'])
                    : $result['_source']['summary']));

            $response['results'][] = [
                'id'           => $result['_id'],
                'title'        => str_replace('</span> <span>', ' ', $title),
                'summary'      => str_replace('</span> <span>', ' ', $summary),
                'type'         => $result['_source']['type'],
                'date'         => $result['_source']['date'],
                'country_code' => $result['_source']['country_code'],
                'country'      => $result['_source']['country'],
            ];
        }

        return $response;
    }

    /**
     * @param string $profileId
     *
     * @return array|null
     */
    public function get($profileId)
    {
        $results = $this->service->execute(Request::METHOD_GET, 'opportunities/' . urlencode($profileId));

        if (array_key_exists('error', $results)) {
            drupal_set_message($results['error'], 'error');
            $results = null;
        }

        return $results;
    }

    /**
     * @return array
     */
    public function getCountryList()
    {
        return $this->service->execute(Request::METHOD_GET, 'countries');
    }

    /**
     * @param string $email
     * @param string $token
     * @param string $profileId
     */
    public function verifyEmail($email, $token, $profileId)
    {
        $params = [
            'template' => 'email-verification-opportunity',
            'email'    => $email,
            'url'      => \Drupal::request()->getSchemeAndHttpHost() .
                Url::fromRoute(
                    'opportunities.details',
                    [
                        'token'     => $token,
                        'profileId' => $profileId,
                    ]
                )->toString(),
        ];

        try {
            $this->service->execute(Request::METHOD_POST, 'email-verification', $params);
        } catch (\Exception $e) {
            drupal_set_message('There was a problem while sending the email, please try later.', 'error');
        }
    }

    /**
     * @param string $email
     *
     * @return array
     */
    public function createLead($email)
    {
        return $this->service->execute(Request::METHOD_POST, 'lead', ['email' => $email]);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function convertLead($data)
    {
        return $this->service->execute(Request::METHOD_POST, 'contact', $data);
    }
}
