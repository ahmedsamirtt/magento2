<?php

namespace TwentyToo\AutoTag\Block;

use Magento\Framework\View\Element\Template;
use Psr\Log\LoggerInterface;

class CustomTab extends Template
{
    protected $_tableName = 'twentytoo_tags';
    protected $logger;

    public function __construct(
        Template\Context $context,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    public function getCustomData($pageTitle)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $connection = $resource->getConnection();

        $select = $connection->select()->from($this->_tableName)
            ->where('order_id = :order_id');

        $staticOrderId = 'dress2';
        $binds = [':order_id' => $staticOrderId];

        $results = $connection->fetchAll($select, $binds);
        $englishTags = json_decode($results[0]['english_tags'], true);
        $arabicTags = json_decode($results[0]['arabic_tags'], true);
        $allTags = [
            'english_tags' => $englishTags,
            'arabic_tags' => $arabicTags
        ];
        // Log the results array
        $this->logger->info('Results array:', $allTags);
        $this->logger->info('page_title----------->:', $page_title);

        return $allTags;
    }
}
