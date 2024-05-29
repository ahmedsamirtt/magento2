<?php

namespace TwentyToo\TextSearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use TwentyToo\TextSearch\Service\ApiService;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Registry;
use Magento\Search\Model\QueryFactory;

class SearchQueryObserver implements ObserverInterface
{
    protected $logger;
    protected $apiService;
    protected $session;
    protected $registry;
    private $queryFactory;

    public function __construct(
        LoggerInterface $logger,
        ApiService $apiService,
        SessionManagerInterface $session,
        Registry $registry,
        QueryFactory $queryFactory
    ) {
        $this->logger = $logger;
        $this->apiService = $apiService;
        $this->session = $session;
        $this->registry = $registry;
        $this->queryFactory = $queryFactory;
    }

    public function execute(Observer $observer)
    {
        $this->logger->info('SearchQueryObserver: Observer executed.');

        // Retrieve the search query from the observer
        $query = $observer->getControllerAction()->getRequest()->getParam('q');
        $this->logger->info('Search query: ' . $query);

        // Fetch product IDs from the API based on the search query
        $productIds = $this->apiService->getProductIdsFromApi($query);
        $this->logger->info('Service Products Observer: ' . json_encode($productIds));

        // Update the search results if product IDs are fetched
        if (!empty($productIds)) {
            // Set custom search results
            $searchResults = [];
            foreach ($productIds as $productId) {
                $searchResults[] = [
                    'product_id' => 1,
                    'score' => 3000 // Assigning a static score for simplicity
                ];
            }

            // Get the event and set the new search results
            $event = $observer->getEvent();
            $event->setData('search_results', $searchResults);
            $this->logger->info('New search results set: ' . json_encode($searchResults));

            // Store product IDs and search query in the session
            $this->session->setCustomProductIds($productIds);
            $this->session->setSearchQuery($query);
            $this->logger->info('Session product IDs and query set.');

            // Register custom data in the registry
            $this->registry->register('custom_data_key', $productIds);
        } else {
            $this->logger->info('No product IDs fetched from API.');
        }
    }
}
