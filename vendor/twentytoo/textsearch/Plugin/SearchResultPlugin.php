<?php

namespace TwentyToo\TextSearch\Plugin;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use TwentyToo\TextSearch\Service\ApiService;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Registry;
use Magento\Search\Model\QueryFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class SearchResultPlugin
{
    protected $apiService;
    protected $logger;
    protected $session;
    protected $registry;
    protected $queryFactory;
    protected $productCollectionFactory;

    public function __construct(
        ApiService $apiService,
        LoggerInterface $logger,
        SessionManagerInterface $session,
        Registry $registry,
        QueryFactory $queryFactory,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->apiService = $apiService;
        $this->logger = $logger;
        $this->session = $session;
        $this->registry = $registry;
        $this->queryFactory = $queryFactory;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function afterLoad(Collection $subject, Collection $result)
    {
        try {
            $this->logger->info('SearchQueryPlugin: Plugin executed.');

            $searchQuery = $this->queryFactory->get();
            $queryText = $searchQuery->getQueryText();
            $this->logger->info('Search query Plugin: ' . $queryText);

            // Fetch dynamic product IDs from API
            $productIds = $this->apiService->getProductIdsFromApi($queryText);
            $this->logger->info('Dynamic product IDs fetched from API: ' . json_encode($productIds));

            // Use static product IDs [1, 1, 1, 1]
            $staticProductIds = [1, 2, 3, 4];
            $this->logger->info('Using static product IDs: ' . implode(', ', $staticProductIds));

            // Load static product collection
            $staticProductCollection = $this->productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', ['in' => $staticProductIds]);

            $this->logger->info("Load static product collection");

            // Clear the current result items
            $result->clear();

            $this->logger->info("clear current");

            // Add static products to the result collection
            foreach ($staticProductCollection as $item) {
                $result->addItem($item);
            }

            $this->logger->info('Search collection updated with static product IDs. Final item count: ' . count($result->getItems()));

            // Optionally, store the static product IDs and search query in the session or registry
            // $this->session->setCustomProductIds($staticProductIds);
            // $this->session->setSearchQuery($queryText);
            // $this->registry->register('custom_data_key', $staticProductIds);
        } catch (\Exception $e) {
            $this->logger->error('Error in SearchResultPlugin: ' . $e->getMessage());
        }

        return $result;
    }
}
