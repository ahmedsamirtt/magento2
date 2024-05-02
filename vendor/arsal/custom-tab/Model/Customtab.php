<?php
namespace Arsal\CustomTab\Model;

use Arsal\CustomTab\Model\ResourceModel\Customtab as ResourceModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Customtab
 * @package Arsal\CustomTab\Model
 */
class Customtab extends AbstractModel implements IdentityInterface
{
    const TYPE_CUSTOM_DESCRIPTION = 0;
    const TYPE_CUSTOM_DATA = 1;
    const TYPE_ATTRIBUTES = 2;

    /**
     * @var string
     */
    protected $_cacheTag = 'customtab';

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return ['customtab_' . $this->getId() . '_' . $this->getData('identifier')];
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
