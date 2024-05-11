<?php
namespace TwentyToo\AutoTag\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class IncrementalLoad implements ObserverInterface
{
    protected $logger;
    protected $httpClient;
    protected $productRepository;
    protected $storeManager;

    public function __construct(
        LoggerInterface $logger,
        \Zend\Http\Client $httpClient,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        // Log product ID
        $this->logger->info('Incremental Product ID: ' . $product->getId());

        // Log product data
        $this->logger->info('Incremental Product Data: ' . json_encode($product->getData()));

        //Prepare data for initial Load
        $parsedData = json_decode(json_encode($product->getData()), true);
        $payload = $this->prepareProductData($product->getId(), $parsedData);

        // Call the initial load API
        $this->callInitialLoadApi($payload);
    }

    private function callInitialLoadApi($preparedData)
    {
        $apiUrl = 'https://apidev.twentytoo.ai/cms/v1/data-load';
        $headers = new \Laminas\Http\Headers();
        $headers->addHeaders([
            'Content-Type' => 'application/json',
            'language' => 'en',
            'x-api-key' => '5abo79x1nc',
            'uploadType' => 'webhook'
        ]);
        $request = new \Zend\Http\Request();
        $request->setUri($apiUrl);
        $request->setMethod(\Zend\Http\Request::METHOD_POST);
        $request->setHeaders($headers);
        $request->setContent(json_encode($preparedData));
    
        $response = $this->httpClient->send($request);
    
        if ($response->isSuccess()) {
            $this->logger->info('API call successful.');
        } else {
            $this->logger->error('API call failed: ' . $response->getBody());
        }
    }

    private function prepareProductData($productId, $productData)
    {
        $imageUrl = $this->getImageUrl($productId, $productData);
        $payload = [
            'title' => $productData['name'],
            'description' => $productData['meta_description'],
            'img' => $imageUrl,
            'id' => $productId,
            // 'target_audience' => $productData['tags']
        ];

        return $payload;
    }

    private function getImageUrl($productId, $productData)
    {
        $imageUrl = '';
        try {
            // Load the product using the product repository
            $product = $this->productRepository->getById($productId);

            // Get the base media URL
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            // Get the product image URL
            $imageUrl = $mediaUrl . 'catalog/product' . $product->getImage();
        } catch (\Exception $e) {
            // Handle any exceptions, such as product not found
            $this->logger->error("Error while fetching product image: " . $e->getMessage());
        }

        $this->logger->info("Exposed Public Image --> " . $imageUrl);
        return $imageUrl;
    }
}
