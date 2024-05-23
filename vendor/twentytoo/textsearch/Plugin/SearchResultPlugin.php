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

        // Check if custom product IDs are set (for example, you can retrieve them from session or a service)
        $productIds = [1, 1, 1]; // This should be dynamically fetched based on your requirements
        if ($productIds) {
            $this->logger->info('Custom Product IDs found: ' . json_encode($productIds));

            // Replace the search results with the custom product IDs by modifying the select query
            $subject->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
            $subject->addFieldToFilter('entity_id', ['in' => $productIds]);
        } else {
            $this->logger->info('No custom Product IDs found.');
        }

        // Proceed with the original load method
        $result = $proceed();

        // Log exiting the plugin
        $this->logger->info('SearchResultPlugin: Exiting the plugin.');

        return $result;
    }
}
