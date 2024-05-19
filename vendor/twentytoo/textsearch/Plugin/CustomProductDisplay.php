<?php
namespace TwentyToo\TextSearch\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;

class CustomProductDisplay
{
    protected $productRepository;
    protected $logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    public function afterGetItems(\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $subject, $result)
    {
        $this->logger->info('Custom products display plugin triggered');

        $productIds = [1, 2]; // Static product IDs for testing purposes
        $this->logger->info('Custom product IDs ----> ' . json_encode($productIds));
        
        $customProducts = [];
        foreach ($productIds as $productId) {
            try {
                $product = $this->productRepository->getById($productId);
                $customProducts[] = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(), // Assuming you want the product's base price
                    'image' => $product->getImage(), // Get the product's image URL
                ];
                $this->logger->info('Product Data: :  ' . json_encode($product));
                $this->logger->info('Loaded product ID: ' . $product->getId());
                $this->logger->info('Product Name: ' . $product->getName());
                $this->logger->info('Product Visibility: ' . $product->getVisibility());
                $this->logger->info('Product Status: ' . $product->getStatus());
                $this->logger->info('Product Image: ' . $product->getImage());
            } catch (\Exception $e) {
                $this->logger->info('Product ID ' . $productId . ' could not be loaded.');
            }
        }

        // Return only the custom products and ignore the original search results
        $this->logger->info('customProducts:  ' . json_encode($customProducts));
        return $customProducts;
    }
}
