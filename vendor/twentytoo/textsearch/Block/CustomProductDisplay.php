<?php
namespace TwentyToo\TextSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ProductFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManagerInterface;

class CustomProductDisplay extends Template
{
    protected $productFactory;
    protected $logger;
    protected $session;

    public function __construct(
        Template\Context $context,
        ProductFactory $productFactory,
        LoggerInterface $logger,
        SessionManagerInterface $session,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->logger = $logger;
        $this->session = $session;
        parent::__construct($context, $data);
    }

    public function getProducts()
    {
        $this->logger->info('Custom Product Display Block');
        $productIds = $this->session->getCustomProductIds();
        $searchQuery = $this->session->getSearchQuery();

        $this->logger->info('Custom products display ----> Product IDs: ' . json_encode($productIds));
        $this->logger->info('Search Query: ' . $searchQuery);
        

        return [1,1,1,1];
    }

    public function getProductImageUrl($product)
    {
        $imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
        $this->logger->info('Product Image URL: ' . $imageUrl);
        return $imageUrl;
    }
}
