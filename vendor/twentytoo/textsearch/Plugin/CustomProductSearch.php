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

    public function beforeSearch(
        \Magento\CatalogSearch\Model\Search\Search $subject,
        $requestName,
        \Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria
    ) {
        $this->logger->info('Custom product search plugin triggered');

        // Static product IDs for testing purposes
        $productIds = [1, 2];
        $this->logger->info('Static product IDs ----> ' . json_encode($productIds));

        // Create a filter for the custom product IDs
        $filter = $this->filterBuilder
            ->setField('entity_id')
            ->setValue($productIds)
            ->setConditionType('in')
            ->create();

        // Create a new search criteria with the custom product IDs
        $this->searchCriteriaBuilder->addFilters([$filter]);
        $newSearchCriteria = $this->searchCriteriaBuilder->create();

        $this->logger->info('Modified search criteria: ' . json_encode($newSearchCriteria->getFilterGroups()));

        // Return the modified search criteria to replace the original
        return [$requestName, $newSearchCriteria];
    }
}
