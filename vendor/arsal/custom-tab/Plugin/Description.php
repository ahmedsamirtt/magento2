<?php
namespace Arsal\CustomTab\Plugin;

use Arsal\CustomTab\Model\TabConfig;

class Description
{
    /**
     * @var TabConfig $tabs
     */
    private $tabs;

    /**
     * Description constructor.
     * @param TabConfig $tabs
     */
    public function __construct(
        TabConfig $tabs
    ) {
        $this->tabs = $tabs;
    }

    /**
     * @param \Magento\Catalog\Block\Product\View\Details $subject
     * @param array $result
     * @return array
     */
    public function afterGetGroupSortedChildNames(
        \Magento\Catalog\Block\Product\View\Details $subject,
        $result
    ) {
        if (!empty($this->tabs->getTabs())) {
            foreach ($this->tabs->getTabs() as $key => $tab) {
                $sortOrder = isset($tab['sortOrder']) ? $tab['sortOrder'] : 45;
                $result = array_merge($result, [ $sortOrder => 'product.info.details.' . $key]);
            }
        }
        return $result;
    }
}
