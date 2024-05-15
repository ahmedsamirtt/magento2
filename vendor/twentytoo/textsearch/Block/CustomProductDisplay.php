<?php
namespace TwentyToo\TextSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Psr\Log\LoggerInterface;

class CustomProductDisplay extends Template
{
    protected $productFactory;
    protected $session;
    protected $logger;

    public function __construct(
        Template\Context $context,
        ProductFactory $productFactory,
        SessionManagerInterface $session,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->session = $session;
        $this->logger = $logger; // Ensure logger is assigned
        parent::__construct($context, $data);
    }

    public function getProducts()
    {
        $productIds = $this->session->getTextSearchProductIds();
        $this->logger->info('Custom products display ----> ' . json_encode($productIds));
        if (!$productIds) {
            return [];
        }

        $products = [];
        foreach ($productIds as $productId) {
            $products[] = $this->productFactory->create()->load($productId);
        }
        return $products;
    }

    public function getProductImageUrl($product)
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
    }
}
