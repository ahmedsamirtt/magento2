<?php
namespace TwentyToo\TextSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Session\SessionManagerInterface;

class CustomProductDisplay extends Template
{
    protected $productFactory;
    protected $session;

    public function __construct(
        Template\Context $context,
        ProductFactory $productFactory,
        SessionManagerInterface $session,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->session = $session;
        parent::__construct($context, $data);
    }

    public function getProducts()
    {
        $productIds = $this->session->getTextSearchProductIds();
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
