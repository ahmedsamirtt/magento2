<?php

namespace TwentyToo\TextSearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class SearchResultObserver implements ObserverInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $this->logger->info('Observer function for frontend is executing ');
        // Get the collection from the event
        $collection = $observer->getEvent()->getCollection();
        $this->logger->info('SearchResultObserver: Loaded product collection: ' . print_r($collection->getData(), true));

        // Additional logic can be added here
    }
}
