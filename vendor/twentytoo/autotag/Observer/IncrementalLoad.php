<?php
namespace TwentyToo\AutoTag\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ProductRepository;
use Laminas\Http\Client as HttpClient;
use Laminas\Http\Headers;
use Laminas\Http\Request;

class IncrementalLoad implements ObserverInterface
{
    protected $logger;
    protected $httpClient;
    protected $productRepository;

    public function __construct(
        LoggerInterface $logger,
        HttpClient $httpClient,
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

        // Prepare data for initial Load
        $parsedData = json_decode(json_encode($product->getData()), true);
        $payload = $this->prepareProductData($product->getId(), $parsedData);

        // Call the initial load API
        $this->callInitialLoadApi($payload);
    }

    private function callInitialLoadApi($preparedData)
    {
        $apiUrl = 'https://api.twentytoo.ai/cms/v1/data-load';
        $headers = new Headers();
        $headers->addHeaders([
            'Content-Type' => 'application/json',
            'language' => 'en',
            'x-api-key' => 'UZAuaaWG1V7DCQfcYFLLw5zzbytoCWqn5y7mwlRU',
            'uploadType' => 'webhook'
        ]);
        $request = new Request();
        $request->setUri($apiUrl);
        $request->setMethod(Request::METHOD_POST);
        $request->setHeaders($headers);
        $request->setContent(json_encode($preparedData));

        $this->httpClient->setRequest($request);
        $response = $this->httpClient->send();

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
        $productId = $productData['entity_id'];
        $product = $this->productRepository->getById($productId);
        $mediaGalleryEntries = $product->getMediaGalleryEntries();
        
        foreach ($mediaGalleryEntries as $mediaGalleryEntry) {
            if (isset($mediaGalleryEntry['file'])) {
                $baseUrl = 'http://ec2-3-139-56-38.us-east-2.compute.amazonaws.com/pub/media/catalog/product';
                $imageUrl = $baseUrl . $mediaGalleryEntry->getFile();
                $this->logger->info("Exposed Public Image --> " . $imageUrl);
                return $imageUrl;
            }
        }
        $this->logger->info("Image not found for Product ID --> " . $productId);
        return '';
    }
}
