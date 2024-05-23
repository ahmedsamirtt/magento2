<?php

namespace TwentyToo\TextSearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use TwentyToo\TextSearch\Service\ApiService;
use Magento\Framework\Session\SessionManagerInterface;

class SearchQueryObserver implements ObserverInterface
{
    protected $logger;
    protected $apiService;
    protected $session;

    public function __construct(
        LoggerInterface $logger,
        ApiService $apiService,
        SessionManagerInterface $session
    ) {
        $this->logger = $logger;
        $this->apiService = $apiService;
        $this->session = $session;
    }

    public function execute(Observer $observer)
    {
        // Log that the observer is being executed
        $this->logger->info('SearchQueryObserver: Observer executed.');

        // Get the search query from the request
        $query = $observer->getData('query');
        $queryText = $query->getQueryText();
        $this->logger->info('Search query: ' . $queryText);

        // Fetch product IDs from the API
        $productIds = $this->apiService->getProductIdsFromApi($queryText);
        $this->logger->info('Service Products: ' . json_encode($productIds));

        // Save the product IDs to the session for use in the plugin
        if (!empty($productIds)) {
            $this->session->setCustomProductIds($productIds);
        }
    }
}
