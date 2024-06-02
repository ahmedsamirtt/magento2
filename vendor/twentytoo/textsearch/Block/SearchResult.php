<?php

namespace TwentyToo\TextSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class SearchResult extends Template
{
    protected $productCollectionFactory;

    public function __construct(
        Template\Context $context,
        CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getLoadedProductCollection()
    {
        // Assuming you want to load a specific set of products by IDs
        $productIds = [1, 3]; // Use your logic to get the product IDs
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
                   ->addFieldToFilter('entity_id', ['in' => $productIds]);

        return $collection;
    }
}
