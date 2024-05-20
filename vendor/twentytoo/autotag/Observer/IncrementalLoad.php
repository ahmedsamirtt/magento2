<?php
namespace TwentyToo\AutoTag\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ProductRepository;
class IncrementalLoad implements ObserverInterface
{
    protected $logger;
    protected $httpClient;
    protected $productRepository;

    public function __construct(
        LoggerInterface $logger,
        \Zend\Http\Client $httpClient,
        ProductRepository $productRepository
    ) {
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->productRepository = $productRepository;
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
            'x-api-key' => 'nm5jubrhx8',
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
        // $imageUrl = '';
        $productId = $productData['entity_id'];
        $product = $this->productRepository->getById($productId);
        $mediaGalleryEntries = $product->getMediaGalleryEntries();
        foreach ($mediaGalleryEntries as $mediaGalleryEntry) {
            if (isset($mediaGalleryEntry['file'])) {
                $baseUrl = 'http://ec2-3-139-56-38.us-east-2.compute.amazonaws.com/pub/media/catalog/product';
                $imageUrl = $baseUrl . $mediaGalleryEntry['file'];
                $this->logger->info("Exposed Public Image --> " . $imageUrl);
                return $imageUrl;
            }
        }
        $this->logger->info("Image not found for Product ID --> " . $productId);
        return '';
    }
}
