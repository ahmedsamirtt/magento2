<?php

namespace TwentyToo\AutoTag\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\HTTP\Client\CurlFactory;

class Tags extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * Constructor.
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param ProductCollectionFactory $productCollectionFactory
     * @param CurlFactory $curlFactory
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        ProductCollectionFactory $productCollectionFactory,
        CurlFactory $curlFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->curlFactory = $curlFactory;
    }

    /**
     * Execute action.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        try {

            // Get product IDs
            $productIds = $this->getAllProductIds();
            $staticProductIds = ["b6f8207f6a029e48a88d5727e97bcfb069f05281", "7b18377ed501e8f34332cea99d882be7a15d1a48"];
            // Make HTTP request
            $response = $this->makeHttpRequest($staticProductIds);

            // Log the response
            $this->logger->info('HTTP request response: ' . $response);

            // Log the product IDs
            $this->logger->info('Product IDs: ' . implode(', ', $productIds));

            // Return success message
            $response = ['success' => true, 'message' => $response];
        } catch (\Exception $e) {
            // Log the error
            $this->logger->error($e->getMessage());

            // Return error message
            $response = ['success' => false, 'message' => 'An error occurred.'];
        }

        return $resultJson->setData($response);
    }

    /**
     * Make HTTP request and return the response.
     *
     * @param array $productIds The array of product IDs.
     * @return string The response from the HTTP request.
     * @throws \Exception If an error occurs during the request.
     */
    protected function makeHttpRequest(array $productIds)
    {
        $curl = $this->curlFactory->create();
        $headers = [
            'api_key' => 'h11lwywxs5'
        ];
        $baseUrl = 'https://api.twentytoo.ai/cms/v1/autotagging/v1/get-tags?product_ids=';
    
        $responses = [];
        foreach ($productIds as $productId) {
            $url = $baseUrl . '["' . $productId . '"]';
            try {
                $curl->setHeaders($headers);
                $curl->get($url);
                $response = $curl->getBody();
                
                // Log the response for the current product
                $this->logger->info('Response for product ID ' . $productId . ': ' . $response);
    
                $responses[] = $response;
            } catch (\Exception $e) {
                // Log error if request fails for the current product
                $this->logger->error('Error for product ID ' . $productId . ': ' . $e->getMessage());
                // Add empty response for failed request
                $responses[] = '';
            }
        }
    
        return implode("\n", $responses);
    }
    

    /**
     * Get all product IDs from Magento products table.
     *
     * @return array Product IDs
     */
    protected function getAllProductIds()
    {
        $productIds = [];

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect('entity_id');

        foreach ($productCollection as $product) {
            $productIds[] = $product->getId();
        }

        return $productIds;
    }
}
