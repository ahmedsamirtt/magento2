<?php

namespace TwentyToo\TextSearch\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\Search\SearchResultFactory;
use TwentyToo\TextSearch\Model\ApiInterface;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

class SearchPlugin
{
    protected $api;
    protected $request;
    protected $productCollectionFactory;
    protected $logger;

    public function __construct(
        ApiInterface $api,
        RequestInterface $request,
        CollectionFactory $productCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->api = $api;
        $this->request = $request;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->logger = $logger;
    }

    public function aroundGetList(
        SearchResultFactory $subject,
        \Closure $proceed,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ) {
        $this->logger->info('SearchPlugin: aroundGetList called'); // Log message

        $query = $this->request->getParam('q'); // Get the search query from the request
        $this->logger->info('SearchPlugin: Query: ' . $query); // Log the search query

        $productIds = $this->api->fetchData($query);
        $this->logger->info('SearchPlugin: Fetched Product IDs: ' . implode(',', $productIds)); // Log the fetched product IDs

        if (!empty($productIds)) {
            $productCollection = $this->productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', ['in' => $productIds]);

            $items = [];
            foreach ($productCollection as $product) {
                $items[] = $product;
            }

            $searchResult = $subject->create(['items' => $items, 'total_count' => count($items)]);
            $this->logger->info('SearchPlugin: Returning search result with items'); // Log returning items
            return $searchResult;
        }

        $this->logger->info('SearchPlugin: Proceeding with default behavior'); // Log default behavior
        return $proceed($searchCriteria);
    }
}
