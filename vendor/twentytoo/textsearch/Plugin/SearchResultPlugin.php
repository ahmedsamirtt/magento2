<?php

namespace TwentyToo\TextSearch\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\Search\SearchResultInterface;

class SearchResultPlugin
{
    protected $productCollectionFactory;

    public function __construct(CollectionFactory $productCollectionFactory)
    {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function aroundGetList(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        callable $proceed,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ) {
        // Skip the original method call to bypass the actual search
        // $result = $proceed($searchCriteria);

        // Load the specific products you want to always return
        $specificProductIds = [1, 3];
        $productCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', ['in' => $specificProductIds]);

        // Create a new search result interface to return
        /** @var SearchResultInterface $result */
        $result = $subject->getList($searchCriteria);
        $result->setItems($productCollection->getItems());
        $result->setTotalCount(count($specificProductIds));

        return $result;
    }
}
