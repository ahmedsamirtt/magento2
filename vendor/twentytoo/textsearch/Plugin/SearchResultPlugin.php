<?php

namespace TwentyToo\TextSearch\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\Search\SearchResultInterface;
use Psr\Log\LoggerInterface;

class SearchResultPlugin
{
    protected $productCollectionFactory;
    protected $logger;

    public function __construct(CollectionFactory $productCollectionFactory, LoggerInterface $logger)
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->logger = $logger;
    }

    public function aroundGetList(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        callable $proceed,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ) {
        // Log the search query
        $this->logger->info('Search query intercepted, replacing with specific product IDs.');

        // Load the specific products you want to always return
        $specificProductIds = [1, 3];
        $productCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', ['in' => $specificProductIds]);

        // Log the number of products found
        $this->logger->info('Loaded specific products: ' . implode(', ', $specificProductIds));

        // Create a new search result interface to return
        /** @var SearchResultInterface $result */
        $result = $subject->getList($searchCriteria);
        $result->setItems($productCollection->getItems());
        $result->setTotalCount(count($specificProductIds));

        // Log the final result count
        $this->logger->info('Returning modified search results with ' . count($specificProductIds) . ' products.');

        return $result;
    }
}
