<?php
namespace Arsal\CustomTab\Model;

use Arsal\CustomTab\Model\ResourceModel\Customtab\Collection;
use Arsal\CustomTab\Model\ResourceModel\Customtab\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;

/**
 * Class TabConfig
 * @package Arsal\CustomTab\Model
 */
class TabConfig
{
    private $tabs = [];
    /**
     * @var null|Product
     */
    private $product = null;

    /**
     * Core registry
     *
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var ResourceModel\Customtab\CollectionFactory
     */
    private $collection;

    /**
     * TabConfig constructor.
     * @param CollectionFactory $collection
     * @param Registry $registry
     */
    public function __construct(
        CollectionFactory $collection,
        Registry $registry
    ) {
        $this->collection = $collection;
        $this->coreRegistry = $registry;
    }

    /**
     * @return array
     */
    public function getTabs()
    {
        /**
         * Return if data already fetched.
         */
        if (!empty($this->tabs)) {
            return $this->tabs;
        }

        $collection = $this->getCollection()->setOrder('sort_order', Collection::SORT_ORDER_ASC);
        if (empty($collection)) {
            return [];
        }

        /**
         * Collect Tabs
         */
        foreach ($collection as $item) {
            $this->tabs[$item->getIdentifier()] = [
                'title'         =>  $item->getTitle(),
                'description'   =>  $item->getDescription(),
                'sortOrder'     =>  (int)$item->getSortOrder()
            ];
            if ($item->getType() == Customtab::TYPE_CUSTOM_DESCRIPTION || !$item->getCustomData()) {
                continue;
            }

            if ($item->getType() == Customtab::TYPE_ATTRIBUTES) {
                if (!empty($item->getCustomData()) && $this->getProduct()) {
                    // Process Product attributes
                    $this->processAttributes($item);
                }
                continue;
            }
            // Create Template
            $this->tabs[$item->getIdentifier()]['type'] = 'template';
            $this->tabs[$item->getIdentifier()]['data'] = \Zend_Json::decode($item->getCustomData());
        }

        return $this->tabs;
    }

    /**
     * @param Customtab $item
     */
    private function processAttributes(Customtab $item)
    {
        $attributes = explode(',', $item->getCustomData());
        if (!empty($attributes)) {
            foreach ($attributes as $attributeCode) {
                if (!isset($this->getProduct()->getAttributes()[trim($attributeCode)]) || !$this->getProduct()->getCustomAttribute(trim($attributeCode))) {
                    continue;
                }

                $this->tabs[$item->getIdentifier()]['description']  .=  "<div class='attribute-information'>"
                        . "<strong>{$this->getProduct()->getAttributes()[trim($attributeCode)]->getDefaultFrontendLabel()}</strong>: "
                        . " {$this->getProduct()->getCustomAttribute(trim($attributeCode))->getValue()}"
                    . "</div>";
            }
        }
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->product) {
            $this->product = $this->coreRegistry->registry('product');
        }
        return $this->product;
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        return $this->collection->create();
    }
}
