<?php

namespace TwentyToo\AutoTag\Block;

use Magento\Framework\View\Element\Template;

class CustomTab extends Template
{
    protected $_tableName = 'twentytoo_tags';
    public function getCustomData()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $connection = $resource->getConnection();

        $select = $connection->select()->from($this->_tableName)
            ->where('order_id = :order_id');

        $staticOrderId = 'dress2';
        $binds = [':order_id' => $staticOrderId];

        $results = $connection->fetchAll($select, $binds);

        return $results;
    }
}
