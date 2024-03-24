<?php
namespace Magento\Framework\View\TemplateEngine\Php;

/**
 * Interceptor class for @see \Magento\Framework\View\TemplateEngine\Php
 */
class Interceptor extends \Magento\Framework\View\TemplateEngine\Php implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\ObjectManagerInterface $helperFactory)
    {
        $this->___init();
        parent::__construct($helperFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\View\Element\BlockInterface $block, $fileName, array $dictionary = [])
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'render');
        if (!$pluginInfo) {
            return parent::render($block, $fileName, $dictionary);
        } else {
            return $this->___callPlugins('render', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $args)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, '__call');
        if (!$pluginInfo) {
            return parent::__call($method, $args);
        } else {
            return $this->___callPlugins('__call', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, '__isset');
        if (!$pluginInfo) {
            return parent::__isset($name);
        } else {
            return $this->___callPlugins('__isset', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, '__get');
        if (!$pluginInfo) {
            return parent::__get($name);
        } else {
            return $this->___callPlugins('__get', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function helper($className)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'helper');
        if (!$pluginInfo) {
            return parent::helper($className);
        } else {
            return $this->___callPlugins('helper', func_get_args(), $pluginInfo);
        }
    }
}
