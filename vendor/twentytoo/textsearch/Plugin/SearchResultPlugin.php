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

    public function afterLoad(Collection $subject, Collection $result)
    {
        try {
            $this->logger->info('SearchQueryPlugin: Plugin executed.');

            $searchQuery = $this->queryFactory->get();
            $queryText = $searchQuery->getQueryText();
            $this->logger->info('Search query Plugin: ' . $queryText);

            $productIds = $this->apiService->getProductIdsFromApi($queryText);
            $this->logger->info('Service Products Plugin: ' . json_encode($productIds));

            if (!empty($productIds)) {
                // Filter the collection to include only the product IDs returned from the API
                $result->addFieldToFilter('entity_id', ['in' => [1,1,1,1]]);
                $this->logger->info('Search collection updated with new product IDs.');

                // Store the product IDs and search query in the session
                $this->session->setCustomProductIds($productIds);
                $this->session->setSearchQuery($queryText);
                $this->registry->register('custom_data_key', $productIds);
            } else {
                $this->logger->info('No product IDs fetched from API.');
            }
        } catch (\Exception $e) {
            $this->logger->error('Error in SearchResultPlugin: ' . $e->getMessage());
        }

        return $result;
    }
}
