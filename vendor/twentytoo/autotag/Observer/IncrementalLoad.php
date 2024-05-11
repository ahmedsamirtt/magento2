<?php
namespace TwentyToo\AutoTag\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class IncrementalLoad implements ObserverInterface
{
    protected $logger;
    protected $httpClient;

    public function __construct(
        LoggerInterface $logger,
        \Zend\Http\Client $httpClient
    ) {
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        // Log product ID
        $this->logger->info('Incremental Product ID: ' . $product->getId());

        // Log product data
        $this->logger->info('Incremental Product Data: ' . json_encode($product->getData()));

        //Prepare data for initial Laod
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
        $imageUrl = $this->getImageUrl($productData);
        $payload = [
            'title' => $productData['name'],
            'description' => $productData['meta_description'],
            'img' => $imageUrl,
            'id' => $productId,
            // 'target_audience' => $productData['tags']
        ];

        return $payload;
    }

    private function getImageUrl($productData)
{
    $imageUrl = '';
    $id = $productData['entity_id'];
    // $imageMetadata = $productData['media_gallery']['images'];
    // foreach ($imageMetadata as $image) {
    //     // Check if the entity ID matches
    //     if (isset($image['entity_id']) && $image['entity_id'] == $id) {
    //         $baseUrl = 'http://ec2-3-139-56-38.us-east-2.compute.amazonaws.com/pub/media/catalog/product';
    //         $imagePath = $image['file'];
    //         $imageUrl = $baseUrl . $imagePath;
    //         break;
    //     }
    // }
    try {
        // Load the product using the product repository
        $product = $this->productrepository->getById($id);

        // Get the base media URL
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

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
