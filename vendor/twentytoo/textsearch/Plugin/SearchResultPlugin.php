<?php

namespace TwentyToo\TextSearch\Plugin;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use TwentyToo\TextSearch\Service\ApiService;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Registry;
use Magento\Search\Model\QueryFactory;

class SearchResultPlugin
{
    protected $apiService;
    protected $logger;
    protected $session;
    protected $registry;
    protected $queryFactory;

    public function __construct(
        ApiService $apiService,
        LoggerInterface $logger,
        SessionManagerInterface $session,
        Registry $registry,
        QueryFactory $queryFactory
    ) {
        $this->apiService = $apiService;
        $this->logger = $logger;
        $this->session = $session;
        $this->registry = $registry;
        $this->queryFactory = $queryFactory;
    }

    public function aroundLoad(
        Collection $subject,
        callable $proceed
    ) {
        try {
            $this->logger->info('SearchResultPlugin: Plugin execution started.');

            // Get the search query text
            $searchQuery = $this->queryFactory->get();
            $queryText = $searchQuery->getQueryText();
            $this->logger->info('Search query: ' . $queryText);

            // Fetch dynamic product IDs from API
            $productIds = $this->apiService->getProductIdsFromApi($queryText);
            $this->logger->info('Dynamic product IDs fetched from API: ' . json_encode($productIds));

            if (!empty($productIds)) {
                // Provide a fallback if no product IDs are returned from the API
                $this->logger->info('No product IDs returned from the API, using fallback product IDs.');
                $productIds = [1]; // Use a default product ID or an empty array to return no results
            }

            // Modify the select statement with the dynamic or fallback product IDs
            $select = $subject->getSelect();
            $this->logger->info('Current select statement before modification: ' . $select->__toString());

            // Reset the WHERE clause and set the new product ID filter
            $select->reset(\Zend_Db_Select::FROM);
            $select->from(['e' => 'catalog_product_entity']);
            $select->where('e.entity_id IN (?)', $productIds);
            $this->logger->info('Simplified select statement: ' . $select->__toString());            

            // Optionally, store the product IDs and search query in the session or registry
            // $this->session->setCustomProductIds($productIds);
            // $this->session->setSearchQuery($queryText);
            // $this->registry->register('custom_data_key', $productIds);

        } catch (\Exception $e) {
            $this->logger->error('Error in SearchResultPlugin: ' . $e->getMessage());
        }

        // Proceed with the original method call
        $result = $proceed();
        
        $this->logger->info('SearchResultPlugin: Plugin execution completed.');
        
        return $result;
    }
}
