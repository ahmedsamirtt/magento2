<?php

namespace TwentyToo\TextSearch\Plugin;

use TwentyToo\TextSearch\Service\ApiService;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Psr\Log\LoggerInterface;

class SearchPlugin
{
    protected $apiService;
    protected $productCollectionFactory;
    protected $logger;

    public function __construct(
        ApiService $apiService,
        ProductCollectionFactory $productCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->apiService = $apiService;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->logger = $logger;
    }

    public function aroundGetItems($subject, callable $proceed)
    {
        // Log that the plugin is being executed
        $this->logger->info('SearchPlugin: Plugin executed.');

        $result = $proceed();
        $query = $subject->getSearchCriteria()->getQuery()->getQueryText(); // Assuming this is how you get the query
        $productIds = $this->apiService->getProductIdsFromApi($query);
        
        if (!empty($productIds)) {
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToFilter('entity_id', ['in' => $productIds]);
            return $productCollection->getItems();
        }

        return $result;
    }
}
