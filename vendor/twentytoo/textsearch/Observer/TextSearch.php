<?php
namespace TwentyToo\TextSearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Zend\Http\Client;
use Psr\Log\LoggerInterface;

class TextSearch implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // Check if the module is enabled in configuration
        if (!$this->scopeConfig->isSetFlag('textsearch/general/enabled')) {
            return;
        }

        // Check if the request is for a search
        $controller = $observer->getControllerAction();
        if ($controller->getRequest()->getFullActionName() == 'catalogsearch_result_index') {
            // Get search query
            $query = $controller->getRequest()->getParam('q');

            // Log search query
            $this->logger->info('search query: ' . $query);

            // Call API with the search query
            $response = $this->callApi($query);

            // Log the response
            $this->logger->info('API Response: ' . json_encode($response));
        }
    }

    /**
 * Call API with the search query
 *
 * @param string $query
 * @return array|null
 */
protected function callApi($query)
{
    // API endpoint
    $apiUrl = 'https://apidev.twentytoo.ai/cms/v2/text-search';

    // API payload
    $payload = [
        'text' => $query,
        'page' => 1,
        'page_limit' => 12,
        'filters' => [
            [
                'website' => []
            ],
            [
                'target_audience' => []
            ],
            [
                'department' => []
            ]
        ]
    ];

    // Set headers
    $headers = [
        'Content-Type' => 'application/json',
        'x-tt-api-key' => '32Z4tGu2GI9zmSPH8aJg06KmAN1ljV0UaOBDOLnp'
    ];

    // Create HTTP client with increased timeout
    // $client = new Client($apiUrl, [
    //    'timeout' => 30 // Set timeout to 30 seconds
    // ]);
    $client = new Client($apiUrl);
    $client->setMethod('POST');
    $client->setHeaders($headers);
    $client->setRawBody(json_encode($payload));
    $client->setEncType('application/json');

    try {
        // Send request and get response
        $response = $client->send();

        // Process response
        if ($response->isSuccess()) {
            return json_decode($response->getBody(), true);
        } else {
            // Handle API error
            $errorMessage = $response->getReasonPhrase();
            $this->logger->error('API Error: ' . $errorMessage);
            return null; // Return null or handle the error accordingly
        }
    } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e) {
        // Log the timeout error
        $this->logger->error('API Timeout: ' . $e->getMessage());
        return null; // Return null or handle the timeout error accordingly
    }
}


}
