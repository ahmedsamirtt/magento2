<?php

namespace TwentyToo\TextSearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use TwentyToo\TextSearch\Service\ApiService;

class SearchQueryObserver implements ObserverInterface
{
    protected $logger;
    protected $apiService;

    public function __construct(
        LoggerInterface $logger,
        ApiService $apiService
    ) {
        $this->logger = $logger;
        $this->apiService = $apiService;
    }

    public function execute(Observer $observer)
    {
        // Log that the observer is being executed
        $this->logger->info('SearchQueryObserver: Observer executed.');

        // Get the search query from the request
        $request = $observer->getData('request');
        $query = $request->getParam('q');
        $this->logger->info('Search query: ' . $query);

        // Fetch product IDs from the API
        $productIds = $this->apiService->getProductIdsFromApi($query);
        $this->logger->info('Service Products: ' . json_encode($productIds));

        if (!empty($productIds)) {
            // Replace search results with the custom product IDs
            $this->replaceSearchResults($observer, [1]);
        }
    }

    protected function replaceSearchResults(Observer $observer, array $productIds)
    {
        $this->logger->info('Replacing search results with custom product IDs.');
        
        // Get the search results collection
        $collection = $observer->getData('collection');

        // Clear existing items and set new product IDs
        $collection->clear();
        $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
        $collection->load();

        // Log the replacement action
        $this->logger->info('Search results replaced with product IDs: ' . implode(', ', $productIds));
    }
}
