<?php

namespace TwentyToo\TextSearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use TwentyToo\TextSearch\Service\ApiService;
use Magento\Framework\App\ResourceConnection;

class SearchQueryObserver implements ObserverInterface
{
    protected $logger;
    protected $apiService;
    protected $resourceConnection;

    public function __construct(
        LoggerInterface $logger,
        ApiService $apiService,
        ResourceConnection $resourceConnection
    ) {
        $this->logger = $logger;
        $this->apiService = $apiService;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(Observer $observer)
    {
        // Log that the observer is being executed
        $this->logger->info('SearchQueryObserver: Observer executed.');
        $controller = $observer->getControllerAction();
        $query = $controller->getRequest()->getParam('q');
        $this->logger->info('Search query: ' . $query);

        $productIds = $this->apiService->getProductIdsFromApi($query);
        $this->logger->info('Service Products ' . json_encode($productIds));

        if (!empty($productIds)) {
            $this->logger->info('Product IDs from API: ' . implode(', ', $productIds));
            $this->replaceSearchResults([1]);
        }
    }

    protected function replaceSearchResults($productIds)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('catalogsearch_fulltext_scope1');

        $query = "DELETE FROM $tableName WHERE product_id NOT IN (" . implode(',', $productIds) . ")";
        $connection->query($query);

        $this->logger->info('Search results replaced with product IDs.');
    }
}
