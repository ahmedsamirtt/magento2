<?php

namespace TwentyToo\TextSearch\Plugin;

use Psr\Log\LoggerInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as SearchCollection;

class SearchResultPlugin
{
    protected $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function aroundLoad(SearchCollection $subject, callable $proceed)
    {
        // Log entering the plugin
        $this->logger->info('SearchResultPlugin: In the plugin.');

        // Use static product IDs for testing
        $productIds = [1, 2];
        $this->logger->info('Static Product IDs for testing: ' . json_encode($productIds));

        // Clear the existing filters and add a new filter with static product IDs
        $subject->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
        $subject->addFieldToFilter('entity_id', ['in' => $productIds]);

        // Proceed with the original load method
        $result = $proceed();

        // Log exiting the plugin
        $this->logger->info('SearchResultPlugin: Exiting the plugin.');

        return $result;
    }
}
