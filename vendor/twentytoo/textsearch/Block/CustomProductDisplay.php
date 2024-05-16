<?php
namespace TwentyToo\TextSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ProductFactory;
use Psr\Log\LoggerInterface;

class CustomProductDisplay extends Template
{
    protected $productFactory;
    protected $logger;

    public function __construct(
        Template\Context $context,
        ProductFactory $productFactory,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    public function getProducts()
    {
        // Static product IDs for testing purposes
        $productIds = [1, 2];
        $this->logger->info('Custom products display ----> ' . json_encode($productIds));
        
        // $products = [];
        // foreach ($productIds as $productId) {
        //     $product = $this->productFactory->create()->load($productId);
        //     if ($product->getId()) {
        //         array_push($products, $product); // Use array_push to add product to array
        //     } else {
        //         $this->logger->info('Product ID ' . $productId . ' could not be loaded.');
        //     }
        // }
        
        return $productIds;
    }

    public function getProductImageUrl($product)
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
    }
}
