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

        $query = $observer->getControllerAction();
        $queryText = $query->getRequest()->getParam('q');
        $this->logger->info('Search query: ' . $queryText);

        $productIds = $this->apiService->getProductIdsFromApi($queryText);
        $this->logger->info('Service Products Observer: ' . json_encode($productIds));
        $searchQuery = $this->queryFactory->get();
        $searchQueryText = $searchQuery->getQueryText();
        $this->logger->info('Original Search Query: ' . $searchQueryText);
        $searchResults = $observer->getEvent()->getSearchResults();
        $searchResults = [
            [
                'product_id' => 1,
                'score' => 100
            ],
            [
                'product_id' => 1,
                'score' => 100
            ],
            [
                'product_id' => 1,
                'score' => 100
            ],
            [
                'product_id' => 1,
                'score' => 100
            ]
        ];

        $observer->getEvent()->setSearchResults($searchResults);
        if (!empty($productIds)) {
            $this->session->setCustomProductIds($productIds);
            $this->session->setSearchQuery($queryText);
            $this->logger->info('Session product IDs and query set.');

            // Register custom data in the registry
            $this->registry->register('custom_data_key', $productIds);
        }
    }
}
