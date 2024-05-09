<?php

namespace TwentyToo\AutoTag\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\HTTP\Client\CurlFactory;
use Zend\Http\Client;
use Laminas\Http\Request;
use Laminas\Http\Headers;

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

            //update or insert response products into twentytoo table
            $responseData = json_decode($response, true);
            
            $this->updateProductAttributes($responseData);


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
         $client = new Client();
         $headers = new Headers(); // Create instance of Headers
     
         // Add headers to the Headers instance
         $headers->addHeaders([
             'api_key' => 'h11lwywxs6'
         ]);
     
         $baseUrl = 'https://api.twentytoo.ai/cms/v1/autotagging/v1/get-tags';
     
         // Build query parameters
         $queryParams = http_build_query(['product_ids' => json_encode($productIds)]);
     
         // Construct URL with query parameters
         $url = $baseUrl . '?' . $queryParams;
     
         try {
             $request = new Request();
             $request->setUri($url);
             $request->setHeaders($headers); // Set headers using the Headers instance
             $request->setMethod(Request::METHOD_GET);
     
             $response = $client->send($request);
     
             // Log base URL
             $this->logger->info('Base URL: ' . $baseUrl);
     
             // Log the response
             $this->logger->info('HTTP request response: ' . $response->getBody());
     
             return $response->getBody();
         } catch (\Exception $e) {
             // Log error if request fails
             $this->logger->error('Error making HTTP request: ' . $e->getMessage());
             throw new \Exception('Error making HTTP request: ' . $e->getMessage());
         }
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

    /** 
     * Loop over array of products and its autotag and insert or update.
     *  
    */
    protected function updateProductAttributes(array $responseData)
    {
        // Initialize Object Manager
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    
        // Get Resource Connection
        $resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $connection = $resource->getConnection();
        foreach ($responseData['message'] as $product) {
            $productId = $product['product_id'];
            $engTags = json_encode($product['eng_tags'], JSON_UNESCAPED_UNICODE); // Encode English tags without escaping Unicode
            $arTags = json_encode($product['ar_tags'], JSON_UNESCAPED_UNICODE); // Encode Arabic tags without escaping Unicode
            
            // Insert into the twentytoo_tags table
            $tableName = $resource->getTableName('twentytoo_tags');
            $sql = "INSERT INTO $tableName (order_id, english_tags, arabic_tags) VALUES (?, ?, ?)";
            try {
                $connection->query($sql, [$productId, $engTags, $arTags]);
                $this->logger->info("Record inserted successfully for product ID: $productId");
            } catch (\Exception $e) {
                $this->logger->error("Error inserting record for product ID: $productId - " . $e->getMessage());
            }
        }
    }
    
    

}
