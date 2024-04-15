<?php

namespace TwentyToo\AutoTag\Block;

use Magento\Framework\View\Element\Template;

class CustomTab extends Template
{
    protected $resourceConnection;

    public function __construct(
        Template\Context $context,
        ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $data);
    }
    public function getCustomData()
    {

        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('twentytoo_tags');
        
        $select = $connection->select()->from($tableName)->where('order_id = ?', "dress2");
        $results = $connection->fetchAll($select);
        return $result;
    }
}
