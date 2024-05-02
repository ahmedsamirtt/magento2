<?php
namespace Arsal\CustomTab\Model\ResourceModel\Customtab;

use Arsal\CustomTab\Model\ResourceModel\Customtab as ResourceModel;
use Arsal\CustomTab\Model\Customtab;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(Customtab::class, ResourceModel::class);
    }
}
