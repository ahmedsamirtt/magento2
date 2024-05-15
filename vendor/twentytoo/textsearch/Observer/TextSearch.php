<?php
namespace TwentyToo\TextSearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Request\Http;

class TextSearch implements ObserverInterface
{
    protected $request;
    protected $scopeConfig;
    protected $logger;
    protected $session;
    protected $productRepository;

    public function __construct(
        Http $request,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        SessionManagerInterface $session,
        ProductRepositoryInterface $productRepository
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->session = $session;
        $this->productRepository = $productRepository;
    }

    public function execute(Observer $observer)
    {
        if (!$this->scopeConfig->isSetFlag('textsearch/general/enabled')) {
            return;
        }

        $controller = $observer->getControllerAction();
        if ($controller->getRequest()->getFullActionName() == 'catalogsearch_result_index') {
            $query = $controller->getRequest()->getParam('q');
            $this->logger->info('search query: ' . $query);
            $response = $this->callApi($query);
            $this->logger->info('API Response: ' . json_encode($response));

            $productIds = isset($response['productIds']) ? $response['productIds'] : [1, 2];  // Fallback to static IDs
            $products = [];
            foreach ($productIds as $productId) {
                try {
                    $product = $this->productRepository->getById($productId);
                    $products[] = $product;
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->logger->error('Product not found: ' . $productId);
                }
            }

            $this->session->setSearchResults($products);
        }
    }

    protected function callApi($query)
    {
        $apiUrl = 'https://apidev.twentytoo.ai/cms/v2/text-search';
        $payload = [
            'text' => $query,
            'page' => 1,
            'page_limit' => 12,
            'filters' => [
                ['website' => []],
                ['target_audience' => []],
                ['department' => []]
            ],
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'x-tt-api-key' => '32Z4tGu2GI9zmSPH8aJg06KmAN1ljV0UaOBDOLnp'
        ];

        $client = new Client($apiUrl, ['timeout' => 30]);
        $client->setMethod('POST');
        $client->setHeaders($headers);
        $client->setRawBody(json_encode($payload));
        $client->setEncType('application/json');

        $response = $client->send();
        $responseData = [];
        if ($response->isSuccess()) {
            $responseData = json_decode($response->getBody(), true);
        } else {
            $errorMessage = $response->getReasonPhrase();
            $this->logger->error('API Error: ' . $errorMessage);
        }

        return $responseData;
    }
}
