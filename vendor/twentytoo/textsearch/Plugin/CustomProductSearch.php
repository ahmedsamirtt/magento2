<?php
namespace TwentyToo\TextSearch\Plugin;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManagerInterface;

class CustomProductSearch
{
    protected $searchCriteriaBuilder;
    protected $filterBuilder;
    protected $logger;
    protected $session;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        LoggerInterface $logger,
        SessionManagerInterface $session
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->logger = $logger;
        $this->session = $session;
    }

    public function beforeLoad(
        \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $subject,
        $printQuery = false,
        $logQuery = false
    ) {
        $this->logger->info('Custom product search plugin triggered');

        // Retrieve product IDs from session
        $productIds = $this->session->getTextSearchProductIds();
        $this->logger->info('Before If Dynamic product IDs ----> ' . json_encode($productIds));
        if (!empty($productIds)) {
            $this->logger->info('Dynamic product IDs ----> ' . json_encode($productIds));
            $subject->addAttributeToFilter('entity_id', ['in' => [1,2]]);
        }

        return [$printQuery, $logQuery];
    }
}
