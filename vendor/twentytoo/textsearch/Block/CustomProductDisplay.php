<?php
namespace TwentyToo\TextSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ProductFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Registry;

class CustomProductDisplay extends Template
{
    protected $productFactory;
    protected $logger;
    protected $registry;

    public function __construct(
        Template\Context $context,
        ProductFactory $productFactory,
        LoggerInterface $logger,
        Registry $registry,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->logger = $logger;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getProducts()
    {
        // Retrieve product IDs from the registry
        $productIds = $this->registry->registry('custom_data_key');
        
        if (!$productIds) {
            $this->logger->info('No product IDs found in the registry.');
            // return [];
        }

        $this->logger->info('Custom products display Block ----> ' . json_encode($productIds));
        $staticIds = [1,1,1,1,1,1,1,1,1,1,1];
        $products = [];
        foreach ($staticIds as $productId) {
            $product = $this->productFactory->create()->load($productId);
            if ($product->getId()) {
                $this->logger->info('Loaded product ID: ' . $product->getId());
                $this->logger->info('Product Name: ' . $product->getName());
                $this->logger->info('Product Visibility: ' . $product->getVisibility());
                $this->logger->info('Product Status: ' . $product->getStatus());
                $this->logger->info('Product Image: ' . $product->getImage());
                array_push($products, $product); // Use array_push to add product to array
            } else {
                $this->logger->info('Product ID ' . $productId . ' could not be loaded.');
            }
        }

        return $products;
    }

    public function getProductImageUrl($product)
    {
        $imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
        $this->logger->info('Product Image URL: ' . $imageUrl);
        return $imageUrl;
    }
}
