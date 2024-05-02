<?php
/**
 * @author Arsalan Ajmal
 * @module Arsal_CustomTab
 */
namespace Arsal\CustomTab\Observer;

use Arsal\CustomTab\Model\TabConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class NewTab implements ObserverInterface
{
    /**
     * @var string PARENT_BlOCK_NAME
     */
    const PARENT_BlOCK_NAME = 'product.info.details';

    /**
     * @var string RENDERING_TEMPLATE
     */
    const RENDERING_TEMPLATE = 'Arsal_CustomTab::tab_renderer.phtml';

    /**
     * @var TabConfig $tabs
     */
    private $tabs;

    /**
     * NewTab constructor.
     * @param TabConfig $tabs
     */
    public function __construct(TabConfig $tabs)
    {
        $this->tabs = $tabs;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $blocks = $layout->getAllBlocks();

        foreach ($blocks as $key => &$block) {
            /** @var \Magento\Framework\View\Element\Template $block */
            if ($block->getNameInLayout() == self::PARENT_BlOCK_NAME) {

                foreach ($this->tabs->getTabs() as $key => $tab) {
                    $block->addChild(
                        $key,
                        \Magento\Catalog\Block\Product\View::class,
                        [
                            'template' => self::RENDERING_TEMPLATE,
                            'title'     =>  $tab['title'],
                            'jsLayout'      =>  [
                                $tab
                            ]
                        ]
                    );
                }
            }
        }
    }
}
