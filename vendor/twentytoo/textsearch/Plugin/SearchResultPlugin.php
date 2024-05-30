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
            $this->logger->info('SearchQueryPlugin: Plugin executed.');

            $searchQuery = $this->queryFactory->get();
            $queryText = $searchQuery->getQueryText();
            $this->logger->info('Search query Plugin: ' . $queryText);

            // Fetch dynamic product IDs from API
            $productIds = $this->apiService->getProductIdsFromApi($queryText);
            $this->logger->info('Dynamic product IDs fetched from API: ' . json_encode($productIds));

            // Use static product IDs
            $staticProductIds = [1];
            $this->logger->info('Using static product IDs: ' . implode(', ', $staticProductIds));

            // Modify the select statement with the static product IDs
            $select = $subject->getSelect();
            $this->logger->info('Current select statement before modification: ' . $select->__toString());

            $select->reset(\Zend_Db_Select::WHERE);
            $select->where('e.entity_id IN (?)', $staticProductIds);
            $this->logger->info('Select statement updated with static product IDs: ' . $select->__toString());

            // Optionally, store the static product IDs and search query in the session or registry
            // $this->session->setCustomProductIds($staticProductIds);
            // $this->session->setSearchQuery($queryText);
            // $this->registry->register('custom_data_key', $staticProductIds);

        } catch (\Exception $e) {
            $this->logger->error('Error in SearchResultPlugin: ' . $e->getMessage());
        }

        $result = $proceed();
        
        return $result;
    }
}
