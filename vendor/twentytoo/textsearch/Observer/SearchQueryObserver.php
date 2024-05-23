<?php

namespace TwentyToo\TextSearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use TwentyToo\TextSearch\Service\ApiService;
use Magento\Framework\Session\SessionManagerInterface;

class SearchQueryObserver implements ObserverInterface
{
    protected $logger;
    protected $apiService;
    protected $session;

    public function __construct(
        LoggerInterface $logger,
        ApiService $apiService,
        SessionManagerInterface $session
    ) {
        $this->logger = $logger;
        $this->apiService = $apiService;
        $this->session = $session;
    }

    public function execute(Observer $observer)
    {
        $this->logger->info('SearchQueryObserver: Observer executed.');

        $query = $observer->getControllerAction();
        $queryText = $query->getRequest()->getParam('q');
        $this->logger->info('Search query: ' . $queryText);

        $productIds = $this->apiService->getProductIdsFromApi($queryText);
        $this->logger->info('Service Products: ' . json_encode($productIds));

        if (!empty($productIds)) {
            $this->session->setCustomProductIds($productIds);
            $this->session->setSearchQuery($queryText);
            $this->logger->info('Session product IDs and query set.');
        }
    }
}
