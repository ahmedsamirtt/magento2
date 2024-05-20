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
        $this->logger->info('static product IDs ----> ' . json_encode($productIds));

        $customProducts = [];
        foreach ($productIds as $productId) {
            try {
                $product = $this->productRepository->getById($productId);
                if ($product) {
                    $customProducts[] = $product;
                    $this->logger->info('Loaded product ID: ' . $product->getId());
                    $this->logger->info('Product Name: ' . $product->getName());
                    $this->logger->info('Product Visibility: ' . $product->getVisibility());
                    $this->logger->info('Product Status: ' . $product->getStatus());
                    $this->logger->info('Product Image: ' . $product->getImage());
                } else {
                    $this->logger->error('Product ID ' . $productId . ' not found.');
                }
            } catch (\Exception $e) {
                $this->logger->error('Error loading product ID ' . $productId . ': ' . $e->getMessage());
            }
        }

        // Return the custom product objects and ignore the original search results
        $this->logger->info('Custom product IDs ----> ' . json_encode(array_map(function($product) {
            return $product->getId();
        }, $customProducts)));

        return $customProducts;
    }
}
