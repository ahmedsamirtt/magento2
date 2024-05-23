<?php

namespace TwentyToo\TextSearch\Plugin;

use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

class SearchResultPlugin
{
    protected $logger;
    protected $session;

    public function __construct(
        LoggerInterface $logger,
        SessionManagerInterface $session
    ) {
        $this->logger = $logger;
        $this->session = $session;
    }

    public function aroundLoad(ProductCollection $subject, callable $proceed)
    {
        // Log entering the plugin
        $this->logger->info('SearchResultPlugin: In the plugin.');

        // Check if custom product IDs are set in the session
        $productIds = $this->session->getCustomProductIds();
        if ($productIds) {
            $this->logger->info('Custom Product IDs found: ' . json_encode($productIds));

            // Replace the search results with the custom product IDs
            $subject->addFieldToFilter('entity_id', ['in' => $productIds]);
        } else {
            $this->logger->info('No custom Product IDs found in session.');
        }

        // Proceed with the original load method
        $result = $proceed();

        // Log exiting the plugin
        $this->logger->info('SearchResultPlugin: Exiting the plugin.');

        return $result;
    }
}
