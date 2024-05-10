<?php
namespace TwentyToo\AutoTag\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class IncrementalLoad implements ObserverInterface
{
    protected $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        // Log product ID
        $this->logger->info('Incremental Product ID: ' . $product->getId());

        // Log product data
        $this->logger->info('Incremental Product Data: ' . json_encode($product->getData()));
    }
}
