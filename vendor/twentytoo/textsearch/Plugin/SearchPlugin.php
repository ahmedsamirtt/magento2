<?php

namespace TwentyToo\TextSearch\Plugin;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use TwentyToo\TextSearch\Model\ApiInterface;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

class SearchPlugin
{
    protected $api;
    protected $request;
    protected $logger;

    public function __construct(ApiInterface $api, RequestInterface $request, LoggerInterface $logger)
    {
        $this->api = $api;
        $this->request = $request;
        $this->logger = $logger;
    }

    public function aroundLoad(Collection $subject, \Closure $proceed)
    {
        $this->logger->info('SearchPlugin triggered.');

        $query = $this->request->getParam('q'); // Get the search query from the request
        $this->logger->info('Search query: ' . $query);

        $productIds = $this->api->fetchData($query);
        $this->logger->info('Product IDs: ' . implode(', ', $productIds));

        if (!empty($productIds)) {
            // Replace the search result with the product IDs from the API
            $subject->addFieldToFilter('entity_id', ['in' => $productIds]);
        }

        return $proceed();
    }
}
