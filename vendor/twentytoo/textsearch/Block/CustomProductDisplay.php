<?php
namespace TwentyToo\TextSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ProductFactory;

class CustomProductDisplay extends Template
{
    protected $productFactory;

    public function __construct(
        Template\Context $context,
        ProductFactory $productFactory,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    public function getProductById($productId)
    {
        return $this->productFactory->create()->load($productId);
    }
}
