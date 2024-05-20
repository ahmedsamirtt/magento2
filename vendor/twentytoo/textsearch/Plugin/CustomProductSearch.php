<?php
namespace TwentyToo\TextSearch\Plugin;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Psr\Log\LoggerInterface;

class CustomProductSearch
{
    protected $searchCriteriaBuilder;
    protected $filterBuilder;
    protected $logger;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        LoggerInterface $logger
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->logger = $logger;
    }

    public function beforeLoad(
        \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $subject,
        $printQuery = false,
        $logQuery = false
    ) {
        $this->logger->info('Custom product search plugin triggered');

        // Static product IDs for testing purposes
        $productIds = [1, 2];
        $this->logger->info('Static product IDs ----> ' . json_encode($productIds));

        // Modify the search criteria to only include custom product IDs
        $subject->addAttributeToFilter('entity_id', ['in' => $productIds]);

        return [$printQuery, $logQuery];
    }
}
