<?php
namespace TwentyToo\TextSearch\Plugin;

use Magento\Catalog\Model\ProductFactory;
use Psr\Log\LoggerInterface;

class CustomProductDisplay
{
    protected $productFactory;
    protected $logger;

    public function __construct(
        ProductFactory $productFactory,
        LoggerInterface $logger
    ) {
        $this->productFactory = $productFactory;
        $this->logger = $logger;
    }

    public function afterGetItems(\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $subject, $result)
    {
        $this->logger->info('Custom products display plugin triggered');

        $productIds = [1, 2]; // Static product IDs for testing purposes
        $this->logger->info('Custom product IDs ----> ' . json_encode($productIds));
        
        $customProducts = [];
        foreach ($productIds as $productId) {
            $product = $this->productFactory->create()->load($productId);
            if ($product->getId()) {
                $this->logger->info('Loaded product ID: ' . $product->getId());
                $this->logger->info('Product Name: ' . $product->getName());
                $this->logger->info('Product Visibility: ' . $product->getVisibility());
                $this->logger->info('Product Status: ' . $product->getStatus());
                $this->logger->info('Product Image: ' . $product->getImage());
                $customProducts[] = $product;
            } else {
                $this->logger->info('Product ID ' . $productId . ' could not be loaded.');
            }
        }

        // Optionally, merge custom products with original search results
        return array_merge($result, $customProducts);
    }
}
