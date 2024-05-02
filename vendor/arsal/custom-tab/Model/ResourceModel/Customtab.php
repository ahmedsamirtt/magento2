<?php
namespace Arsal\CustomTab\Model\ResourceModel;

use Arsal\CustomTab\Model\ResourceModel\Customtab\Collection;

class Customtab extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('customtab', 'entity_id');
    }
}
