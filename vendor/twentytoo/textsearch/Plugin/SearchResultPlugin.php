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

            // Provide a fallback if no product IDs are returned from the API
            if (empty($productIds)) {
                $this->logger->info('No product IDs returned from the API, using fallback product IDs.');
                $productIds = [1, 3]; // Use a default product ID or an empty array to return no results
            }

            // Modify the select statement with the dynamic or fallback product IDs
            $select = $subject->getSelect();
            $this->logger->info('Current select statement before modification: ' . $select->__toString());

            // Reset the SELECT, FROM, and WHERE clauses to avoid duplications
            $select->reset(\Zend_Db_Select::COLUMNS);
            $select->reset(\Zend_Db_Select::FROM);
            $select->reset(\Zend_Db_Select::WHERE);
            $select->reset(\Zend_Db_Select::ORDER);

            $select->from(['e' => 'catalog_product_entity'])
                ->join(
                    ['price_index' => 'catalog_product_index_price'],
                    'price_index.entity_id = e.entity_id AND price_index.customer_group_id = 0 AND price_index.website_id = 1',
                    [
                        'price', 'tax_class_id', 'final_price',
                        new \Zend_Db_Expr('IF(price_index.tier_price IS NOT NULL, LEAST(price_index.min_price, price_index.tier_price), price_index.min_price) AS minimal_price'),
                        'min_price', 'max_price', 'tier_price'
                    ]
                )
                ->join(
                    ['cat_index' => 'catalog_category_product_index_store1'],
                    'cat_index.product_id = e.entity_id AND cat_index.store_id = 1 AND cat_index.visibility IN (3, 4) AND cat_index.category_id = 2',
                    ['position AS cat_index_position']
                )
                ->joinLeft(
                    ['review_summary' => 'review_entity_summary'],
                    'e.entity_id = review_summary.entity_pk_value AND review_summary.store_id = 1 AND review_summary.entity_type = (SELECT entity_id FROM review_entity WHERE entity_code = "product")',
                    [new \Zend_Db_Expr('IFNULL(review_summary.reviews_count, 0) AS reviews_count'), new \Zend_Db_Expr('IFNULL(review_summary.rating_summary, 0) AS rating_summary')]
                )
                ->join(
                    ['stock_status_index' => 'cataloginventory_stock_status'],
                    'e.entity_id = stock_status_index.product_id AND stock_status_index.website_id = 0 AND stock_status_index.stock_id = 1',
                    ['stock_status AS is_salable']
                );

            // Apply the product ID filter
            $select->where('e.entity_id IN (?)', $productIds);

            // Apply sorting and limiting
            $select->order('e.entity_id DESC')->limit(12);
            $this->logger->info('Simplified select statement: ' . $select->__toString());

        } catch (\Exception $e) {
            $this->logger->error('Error in SearchResultPlugin: ' . $e->getMessage());
        }

        // Proceed with the original method call
        $result = $proceed();
        $this->logger->info('SearchResultPlugin: Result after proceeding: ' . print_r($result->getData(), true));
        $this->logger->info('SearchResultPlugin: Plugin execution completed.');
        
        return $result;
    }
}
