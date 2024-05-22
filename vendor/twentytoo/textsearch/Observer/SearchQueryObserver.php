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

        $query = $observer->getEvent()->getData('query')->getQueryText();
        $this->logger->info('Search query: ' . $query);

        $productIds = $this->apiService->getProductIdsFromApi($query);

        // Replace search results with the product IDs
        $searchResult = $observer->getEvent()->getData('search_result');
        $searchResult->addAttributeToFilter('entity_id', ['in' => $productIds]);
    }
}
